<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Admin\Menu;

class MenuHelper
{
    /**
     * @param array<int, MenuItem> $menu
     */
    public static function getMenuIndex(string $id, array $menu): int|false
    {
        foreach ($menu as $index => $item) {
            if ($item->getId() == $id) {
                return $index;
            }
        }

        return false;
    }
}
