<?php

namespace Softspring\CmsBundle\Data\FieldTransformer;

use Exception;
use Softspring\CmsBundle\Data\ReferencesRepository;
use Softspring\MediaBundle\Model\MediaInterface;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
class MediaFieldTransformer implements FieldTransformerInterface
{
    public function __construct(protected ?string $mediaStoragePath = null)
    {
    }

    public static function getPriority(): int
    {
        return 100;
    }

    public function supportsExport(string $type, mixed $data): bool
    {
        return $data instanceof MediaInterface;
    }

    /**
     * @throws Exception
     */
    public function export(mixed $data, &$files = []): mixed
    {
        $versionFiles = [];

        // TODO SET THIS IN A LOOP WITH ALL UPLOADED FILES (NOT ONLY _ORIGINAL FILE)
        $originalVersion = $data->getVersion('_original');
        $mediaFileName = 'media/'.$data->getId().([
            'image/jpeg' => '.jpeg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'image/webp' => '.webp',
            'video/webm' => '.webm',
        ][$originalVersion->getFileMimeType()] ?? '');
        $versionFiles['_original'] = $mediaFileName;

        $url = $originalVersion->getUrl();
        $protocol = explode('://', $url)[0];
        $urlWithoutProtocol = substr($url, strlen($protocol) + 3);
        $urlParts = explode('/', $urlWithoutProtocol);
        if (in_array($protocol, ['gs', 'gcs'])) {
            $bucket = array_shift($urlParts);
            $files[$mediaFileName] = [
                '@type' => 'file',
                '@location' => 'gcs',
                'bucket' => $bucket,
                'object' => implode('/', $urlParts),
            ];
        } elseif ('sfs-media-filesystem' === $protocol) {
            $fileName = array_pop($urlParts);
            $files[$mediaFileName] = [
                '@type' => 'file',
                '@location' => 'sfs-media-filesystem',
                'path' => $this->mediaStoragePath.'/'.implode('/', $urlParts),
                'object' => $fileName,
            ];
        } else {
            throw new Exception(sprintf('Media %s protocol not supported for url %s', $protocol, $url));
        }

        $files['media/'.$data->getId().'.json'] = [
            '@type' => 'json',
            'json' => [
                'id' => $data->getId(),
                'type' => $data->getType(),
                'media_type' => $data->getMediaType(),
                'name' => $data->getName(),
                'description' => $data->getDescription(),
                'versionFiles' => $versionFiles,
            ],
        ];

        return [
            '_reference' => 'media___'.$data->getId(),
        ];
    }

    public function supportsImport(string $type, mixed $data): bool
    {
        return isset($data['_reference']) && str_starts_with($data['_reference'], 'media___');
    }

    public function import(mixed $data, ReferencesRepository $referencesRepository, array $options = []): mixed
    {
        return $referencesRepository->getReference($data['_reference'], true);
    }
}
