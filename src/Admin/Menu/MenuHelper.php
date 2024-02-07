<?php

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
