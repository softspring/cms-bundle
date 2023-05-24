<?php

namespace Softspring\CmsBundle\Data;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Data\EntityTransformer\ContentEntityTransformerInterface;
use Softspring\CmsBundle\Data\EntityTransformer\EntityTransformerInterface;
use Softspring\CmsBundle\Data\Exception\DataTransformerNotFoundException;
use Softspring\CmsBundle\Manager\SiteManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\MediaBundle\EntityManager\MediaManagerInterface;

class DataImporter extends AbstractDataImportExport
{
    protected EntityManagerInterface $em;

    protected ReferencesRepository $referenceRepository;
    protected MediaManagerInterface $mediaManager;

    protected CmsConfig $cmsConfig;

    protected SiteManagerInterface $siteManager;

    /**
     * @param EntityTransformerInterface[] $entityTransformers
     */
    public function __construct(EntityManagerInterface $em, iterable $entityTransformers, CmsConfig $cmsConfig, SiteManagerInterface $siteManager, MediaManagerInterface $mediaManager)
    {
        $this->em = $em;
        $this->mediaManager = $mediaManager;
        $this->cmsConfig = $cmsConfig;
        $this->siteManager = $siteManager;
        parent::__construct($entityTransformers);
        $this->referenceRepository = new ReferencesRepository();
    }

    /**
     * @throws DataTransformerNotFoundException
     */
    public function import(array $contents, array $options = []): void
    {
        // preload sites
        foreach ($this->cmsConfig->getSites() as $previousExistingSite) {
            // create sites again because they will be removed by fixtures purge
            $newSite = $this->siteManager->createEntity();
            $newSite->setId("$previousExistingSite");
            $newSite->setConfig($previousExistingSite->getConfig());
            $this->referenceRepository->addReference("site___{$newSite}", $newSite);
            $this->em->persist($newSite);
        }

        // do preloading
        foreach ($contents as $type => $elements) {
            foreach ($elements as $data) {
                $this->getDataTransformer($type, $data)->preload($data, $this->referenceRepository);
            }
        }

        // import medias before to prevent errors
        foreach ($contents['media'] ?? [] as $data) {
            $entity = $this->getDataTransformer('media', $data)->import($data, $this->referenceRepository, $options);
            $this->em->persist($entity);
        }
        $this->em->flush();
        unset($contents['media']);

        // import parent routes before to allow import children
        foreach ($contents['routes'] ?? [] as $routeData) {
            if (RouteInterface::TYPE_PARENT_ROUTE === $routeData['route']['type']) {
                $parentRoute = $this->getDataTransformer('routes', $routeData)->import($routeData, $this->referenceRepository, $options);
                $this->em->persist($parentRoute);
            }
        }
        $this->em->flush();

        // import rest of contents
        foreach ($contents as $type => $elements) {
            foreach ($elements as $data) {
                if ('routes' !== $type || RouteInterface::TYPE_PARENT_ROUTE !== $data['route']['type']) {
                    $entity = $this->getDataTransformer($type, $data)->import($data, $this->referenceRepository, $options);
                    $this->em->persist($entity);
                }
            }
        }
        $this->em->flush();
    }

    public function importVersion(string $type, ContentInterface $content, array $versionData, array $zipData, array $options = []): ?ContentVersionInterface
    {
        /** @var ContentEntityTransformerInterface $transformer */
        $transformer = $this->getDataTransformer($type);

        if (!$transformer instanceof ContentEntityTransformerInterface) {
            throw new \Exception('Invalid entity transformer, check configuration');
        }

        foreach ($zipData['media'] as $id => $mediaData) {
            $media = $this->mediaManager->getRepository()->findOneById($id);

            if ($media) {
                $this->referenceRepository->addReference("media___{$id}", $media);
            } else {
                $this->getDataTransformer('media', $mediaData)->preload($mediaData, $this->referenceRepository);
                $entity = $this->getDataTransformer('media', $mediaData)->import($mediaData, $this->referenceRepository, $options);
                $this->em->persist($entity);
            }
        }

        return $transformer->importVersion($content, $versionData['layout'], $versionData['data'] ?? [], $this->referenceRepository, $options);
    }
}
