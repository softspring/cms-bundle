<?php

namespace Softspring\CmsBundle\Data\EntityTransformer;

use Softspring\CmsBundle\Data\DataTransformer;
use Softspring\CmsBundle\Data\Exception\InvalidElementException;
use Softspring\CmsBundle\Data\Exception\ReferenceNotFoundException;
use Softspring\CmsBundle\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Utils\Slugger;
use Softspring\MediaBundle\EntityManager\MediaManagerInterface;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
abstract class ContentEntityTransformer implements ContentEntityTransformerInterface
{
    protected ContentManagerInterface $contentManager;
    protected RouteManagerInterface $routeManager;
    protected MediaManagerInterface $mediaManager;
    protected SiteManagerInterface $siteManager;
    protected DataTransformer $dataTransformer;

    public function __construct(ContentManagerInterface $contentManager, RouteManagerInterface $routeManager, MediaManagerInterface $mediaManager, SiteManagerInterface $siteManager, DataTransformer $dataTransformer)
    {
        $this->contentManager = $contentManager;
        $this->routeManager = $routeManager;
        $this->mediaManager = $mediaManager;
        $this->siteManager = $siteManager;
        $this->dataTransformer = $dataTransformer;
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supports(string $type, $data = null): bool
    {
        if ('contents' === $type) {
            return true;
        }

        if ('page' === $type) {
            return true;
        }

        return false;
    }

    public function export(object $element, &$files = [], ?object $contentVersion = null, ?string $contentType = null): array
    {
        if (!$element instanceof ContentInterface) {
            throw new InvalidElementException(sprintf('%s dumper requires that $element to be an instance of %s, %s given.', get_called_class(), ContentInterface::class, get_class($element)));
        }
        if ($contentVersion && !$contentVersion instanceof ContentVersionInterface) {
            throw new InvalidElementException(sprintf('%s dumper requires that $contentVersion to be an instance of %s, %s given.', get_called_class(), ContentVersionInterface::class, get_class($contentVersion)));
        }
        if (!$contentType) {
            throw new InvalidElementException(sprintf('%s dumper requires $contentType', get_called_class()));
        }

        $content = $element;

        $files = [];

        $versions = [];

        if ($contentVersion) {
            $versions[] = [
                'layout' => $contentVersion->getLayout(),
                'data' => $this->dataTransformer->export($contentVersion->getData(), $files),
                'version_number' => $contentVersion->getVersionNumber(),
                'origin' => $contentVersion->getOrigin(),
                'origin_description' => $contentVersion->getOriginDescription(),
                'note' => $contentVersion->getNote(),
                'created_at' => $contentVersion->getCreatedAt() ? $contentVersion->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'meta' => $contentVersion->getMeta(),
            ];
        }

        return [
            $contentType => [
                'name' => $content->getName(),
                'default_locale' => $content->getDefaultLocale(),
                'locales' => $content->getLocales(),
                'sites' => $content->getSites()->map(fn (SiteInterface $site) => $site->getId())->toArray(),
                'extra' => $content->getExtraData(),
                'indexing' => $content->getIndexing(),
                'versions' => $versions,
            ],
        ];
    }

    public function preload(array $data, ReferencesRepository $referencesRepository): void
    {
        $contentType = key($data);
        $contentData = $data[$contentType];
        $id = Slugger::lowerSlug($contentData['name']);

        $referencesRepository->addReference("content___{$id}", $this->contentManager->createEntity($contentType));
    }

    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): ContentInterface
    {
        $contentType = key($data);
        $contentData = $data[$contentType];
        $id = Slugger::lowerSlug($contentData['name']);

        try {
            /** @var ContentInterface $content */
            $content = $referencesRepository->getReference("content___{$id}", true);
        } catch (ReferenceNotFoundException $e) {
            throw new RunPreloadBeforeImportException('You must call preload() method before importing', 0, $e);
        }

        // cleanup versions
        $content->removeVersion($content->getVersions()->first());

        $content->setName($contentData['name']);

        // @deprecated: FIX OLD FIXTURES
        $contentData['sites'] = isset($contentData['site']) ? [$contentData['site']] : $contentData['sites'];

        foreach ($contentData['sites'] as $site) {
            $content->addSite($referencesRepository->getReference("site___{$site}", true));
        }

        $content->setDefaultLocale($contentData['default_locale'] ?? null);
        $content->setLocales($contentData['locales'] ?? []);

        $content->setExtraData($contentData['extra']);

        if (isset($contentData['seo'])) {
            $content->setSeo($contentData['seo']);
        }
        $content->setIndexing($contentData['indexing'] ?? []);

        foreach ($contentData['versions'] as $version) {
            if ($version['layout'] && $version['data']) {
                $version = $this->importVersion($content, $version['layout'], $version['data'], $referencesRepository, $options);
                if ($options['auto_publish_version'] ?? false) {
                    $content->setPublishedVersion($version);
                }
            }
        }

        return $content;
    }

    public function importVersion(ContentInterface $content, string $layout, array $data, ReferencesRepository $referencesRepository, array $options = []): ContentVersionInterface
    {
        $version = $this->contentManager->createVersion($content, null, $options['version_origin'] ?? ContentVersionInterface::ORIGIN_UNKNOWN);
        $version->setLayout($layout);
        $version->setData($this->dataTransformer->import($data, $referencesRepository, $options));

        // TODO set version number and other metadata fields

        return $version;
    }
}
