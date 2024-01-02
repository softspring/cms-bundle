<?php

namespace Softspring\CmsBundle\Data\EntityTransformer;

use Softspring\CmsBundle\Data\Exception\ReferenceNotFoundException;
use Softspring\CmsBundle\Data\Exception\RunPreloadBeforeImportException;
use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\MediaBundle\EntityManager\MediaManagerInterface;
use Softspring\MediaBundle\EntityManager\MediaVersionManagerInterface;
use Softspring\MediaBundle\Model\MediaInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
class MediaEntityTransformer implements EntityTransformerInterface
{
    protected MediaManagerInterface $mediaManager;
    protected MediaVersionManagerInterface $mediaVersionManager;

    public function __construct(MediaManagerInterface $mediaManager, MediaVersionManagerInterface $mediaVersionManager)
    {
        $this->mediaManager = $mediaManager;
        $this->mediaVersionManager = $mediaVersionManager;
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function supports(string $type, $data = null): bool
    {
        if ('media' === $type) {
            return true;
        }

        return false;
    }

    public function export(object $element, &$files = []): array
    {
        // TODO: Implement export() method.
        return [];
    }

    public function preload(array $data, ReferencesRepository $referencesRepository): void
    {
        $media = $this->mediaManager->createEntity();
        $media->setDescription($data['media']['id']);
        $referencesRepository->addReference("media___{$data['media']['id']}", $media);
    }

    public function import(array $data, ReferencesRepository $referencesRepository, array $options = []): MediaInterface
    {
        $mediaData = $data['media'];

        try {
            /** @var MediaInterface $media */
            $media = $referencesRepository->getReference("media___{$mediaData['id']}", true);
        } catch (ReferenceNotFoundException $e) {
            throw new RunPreloadBeforeImportException('You must call preload() method before importing', 0, $e);
        }

        $media->setMediaType($data['media']['media_type']);
        $media->setType($data['media']['type']);
        $media->setName($data['media']['name']);
        $media->setDescription($data['media']['description']);

        foreach ($data['media']['versionFiles'] as $versionKey => $versionFileName) {
            $file = $data['files'][$versionFileName];

            $version = $this->mediaVersionManager->createEntity();
            $version->setVersion($versionKey);
            $version->setUpload(new UploadedFile($file['tmpPath'], $file['name']), true);
            $media->addVersion($version);
        }

        return $media;
    }
}
