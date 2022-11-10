<?php

namespace Softspring\CmsBundle\Data\Transformer;

use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Utils\Slugger;
use Softspring\MediaBundle\Model\MediaInterface;

abstract class AbstractDataTransformer implements DataTransformerInterface
{
    public static function getPriority(): int
    {
        return 0;
    }

    public function exportData($data, &$files = [])
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->exportData($value, $files);
            }
        }

        if ($data instanceof RouteInterface) {
            return [
                '_reference' => 'route___'.$data->getId(),
            ];
        }

        if ($data instanceof BlockInterface) {
            return [
                '_reference' => 'block___'.Slugger::lowerSlug($data->getName()),
            ];
        }

        if ($data instanceof MediaInterface) {
//            !is_dir('/srv/cms/fixtures/media') && mkdir('/srv/cms/fixtures/media', 0755, true);

            $versionFiles = [];

            // TODO SET THIS IN A LOOP WITH ALL UPLOADED FILES (NOT ONLY _ORIGINAL FILE)
            $originalVersion = $data->getVersion('_original');
            $parts = explode('/', $originalVersion->getUrl(), 4);
            $mediaFileName = 'media/'.$data->getId().([
                    'image/jpeg' => '.jpeg',
                    'image/png' => '.png',
                    'image/gif' => '.gif',
                    'image/webp' => '.webp',
                    'video/webm' => '.webm',
                ][$originalVersion->getFileMimeType()] ?? '');
            $versionFiles['_original'] = $mediaFileName;

            $files[$mediaFileName] = [
                '@type' => 'file',
                '@location' => 'gcs',
                'bucket' => $parts[2],
                'object' => $parts[3],
            ];

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

        return $data;
    }
}
