<?php

namespace Softspring\CmsBundle\Dumper\Model;

use Softspring\CmsBundle\Dumper\Exception\InvalidElementException;

interface ContentDumperInterface extends DumperInterface
{
    /**
     * @throws InvalidElementException
     */
    public static function dump(object $content, &$files = [], object $contentVersion = null, string $contentType = null): array;
}
