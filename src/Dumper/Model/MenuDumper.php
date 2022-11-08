<?php

namespace Softspring\CmsBundle\Dumper\Model;

use Softspring\CmsBundle\Dumper\Exception\InvalidElementException;
use Softspring\CmsBundle\Model\MenuInterface;

class MenuDumper extends AbstractDumper
{
    public static function dump(object $element, &$files = []): array
    {
        if (!$element instanceof MenuInterface) {
            throw new InvalidElementException(sprintf('%s dumper requires that $element to be an instance of %s, %s given.', get_called_class(), MenuInterface::class, get_class($element)));
        }
        $menu = $element;

        $dump = [
            'menu' => [
                'type' => $menu->getType(),
                'name' => $menu->getName(),
                'items' => [],
            ],
        ];

        foreach ($menu->getItems() as $item) {
            $dump['menu']['items'][] = [
                'text' => $item->getText(),
                'route' => self::dumpData($item->getRoute(), $files),
            ];
        }

        return $dump;
    }
}
