<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Serialization;

use Softspring\CmsBundle\Model\MenuInterface;
use Softspring\CmsBundle\Model\MenuItemInterface;

class MenuSerializer
{
    public function summary(MenuInterface $menu): array
    {
        return [
            'id' => $menu->getId(),
            'type' => $menu->getType(),
            'name' => $menu->getName(),
        ];
    }

    public function detail(MenuInterface $menu): array
    {
        return $this->summary($menu) + [
            'data' => $menu->getData(),
            'items' => array_map(
                fn (MenuItemInterface $item): array => $this->item($item),
                $menu->getItems()?->filter(static fn (MenuItemInterface $item): bool => !$item->getParent() instanceof MenuItemInterface)->toArray() ?? [],
            ),
        ];
    }

    public function item(MenuItemInterface $item): array
    {
        return [
            'id' => $item->getId(),
            'type' => $item->getType(),
            'text' => $item->getText(),
            'symfonyRoute' => $item->getSymfonyRoute(),
            'options' => $item->getOptions(),
            'items' => array_map(
                fn (MenuItemInterface $child): array => $this->item($child),
                $item->getItems()?->toArray() ?? [],
            ),
        ];
    }
}
