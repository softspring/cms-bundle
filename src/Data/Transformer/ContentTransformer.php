<?php

namespace Softspring\CmsBundle\Data\Transformer;

use Softspring\CmsBundle\Data\Exception\InvalidElementException;
use Softspring\CmsBundle\Data\Exception\ReferenceNotFoundException;
use Softspring\CmsBundle\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Utils\Slugger;
use Softspring\MediaBundle\EntityManager\MediaManagerInterface;

abstract class ContentTransformer extends AbstractDataTransformer implements ContentDataTransformerInterface
{
    protected ContentManagerInterface $contentManager;
    protected RouteManagerInterface $routeManager;
    protected MediaManagerInterface $mediaManager;

    public function __construct(ContentManagerInterface $contentManager, RouteManagerInterface $routeManager, MediaManagerInterface $mediaManager)
    {
        $this->contentManager = $contentManager;
        $this->routeManager = $routeManager;
        $this->mediaManager = $mediaManager;
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

    public function export(object $element, &$files = [], object $contentVersion = null, string $contentType = null): array
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
                'data' => $this->exportData($contentVersion->getData(), $files),
            ];
        }

        return [
            $contentType => [
                'name' => $content->getName(),
                'site' => $content->getSite(),
                'extra' => $content->getExtraData(),
                'seo' => $content->getSeo(),
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

        $content->setSite($contentData['site']);
        $content->setName($contentData['name']);
        $content->setExtraData($contentData['extra']);
        $content->setSeo($contentData['seo']);

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
        $this->replaceModuleFixtureReferences($data, $referencesRepository);
        $version->setData($data);

        return $version;
    }

    protected function replaceModuleFixtureReferences(&$data, ReferencesRepository $referencesRepository)
    {
        if (!is_array($data)) {
            return;
        }

        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                if (isset($value['_reference'])) {
                    try {
                        $entity = $referencesRepository->getReference($value['_reference'], true);
                    } catch (ReferenceNotFoundException $e) {
                        if ('route___' === substr($value['_reference'], 0, 8)) {
                            $entity = $this->routeManager->getRepository()->findOneById(substr($value['_reference'], 8));
                        } elseif ('media___' === substr($value['_reference'], 0, 8)) {
                            $entity = $this->mediaManager->getRepository()->findOneById(substr($value['_reference'], 8));
                        } else {
                            $entity = null;
                        }

                        if (!$entity) {
                            throw $e;
                        }
                    } finally {
                        if ($entity ?? false) {
                            $value = $entity;
                        }
                    }
                } else {
                    $this->replaceModuleFixtureReferences($value, $referencesRepository);
                }
            }
        }
    }
}
