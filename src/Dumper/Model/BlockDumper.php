<?php

namespace Softspring\CmsBundle\Dumper\Model;

use Softspring\CmsBundle\Dumper\Exception\InvalidElementException;
use Softspring\CmsBundle\Model\BlockInterface;

class BlockDumper extends AbstractDumper
{
    public static function dump(object $element, &$files = []): array
    {
        if (!$element instanceof BlockInterface) {
            throw new InvalidElementException(sprintf('%s dumper requires that $element to be an instance of %s, %s given.', get_called_class(), BlockInterface::class, get_class($element)));
        }
        $block = $element;

        $dump = [
            'block' => [
                'type' => $block->getType(),
                'name' => $block->getName(),
                'data' => self::dumpData($block->getData(), $files),
            ],
        ];

        return $dump;
    }
}
