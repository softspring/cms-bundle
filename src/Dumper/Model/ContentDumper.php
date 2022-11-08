<?php

namespace Softspring\CmsBundle\Dumper\Model;

use Softspring\CmsBundle\Dumper\Exception\InvalidElementException;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;

abstract class ContentDumper extends AbstractDumper implements ContentDumperInterface
{
    public static function dump(object $content, &$files = [], object $contentVersion = null, string $contentType = null): array
    {
        if (!$content instanceof ContentInterface) {
            throw new InvalidElementException(sprintf('%s dumper requires that $content to be an instance of %s, %s given.', get_called_class(), ContentInterface::class, get_class($content)));
        }
        if (!$contentVersion instanceof ContentVersionInterface) {
            throw new InvalidElementException(sprintf('%s dumper requires that $content to be an instance of %s, %s given.', get_called_class(), ContentVersionInterface::class, get_class($content)));
        }
        if (!$contentType) {
            throw new InvalidElementException(sprintf('%s dumper requires $contentType', get_called_class()));
        }

        $files = [];

        $dump = [
            $contentType => [
                'name' => $content->getName(),
                'site' => $content->getSite(),
                'extra' => $content->getExtraData(),
                'seo' => $content->getSeo(),
                'versions' => [
                    [
                        'layout' => $contentVersion->getLayout(),
                        'data' => self::dumpData($contentVersion->getData(), $files),
                    ],
                ],
            ],
        ];

        return $dump;
    }
}
