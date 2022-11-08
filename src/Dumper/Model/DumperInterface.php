<?php

namespace Softspring\CmsBundle\Dumper\Model;

use Softspring\CmsBundle\Dumper\Exception\InvalidElementException;

interface DumperInterface
{
    /**
     * @throws InvalidElementException
     */
    public static function dump(object $element, &$files = []): array;
}
