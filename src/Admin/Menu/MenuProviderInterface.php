<?php

namespace Softspring\CmsBundle\Admin\Menu;

interface MenuProviderInterface
{
    public static function getPriority(): int;

    /**
     * @param  array<int, MenuItem> $menu
     * @return array<int, MenuItem>
     */
    public function getMenu(array $menu, ?string $currentSelection = null, ?object $entity = null): array;

    public function supports(string $menuId, object $entity): bool;
}
