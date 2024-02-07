<?php

namespace Softspring\CmsBundle\Admin\Menu;

/**
 * @internal
 */
interface GenericMenuProviderInterface
{
    public static function getPriority(): int;

    /**
     * @param  array<int, MenuItem> $menu
     * @return array<int, MenuItem>
     */
    public function getMenu(array $menu, ?string $currentSelection = null, array $context = []): array;
}
