<?php

namespace Softspring\CmsBundle\Admin\Menu;

class MenuManager
{
    /**
     * @param iterable<int, MenuProviderInterface> $menuProviders
     */
    public function __construct(
        protected iterable $menuProviders,
    ) {
    }

    /**
     * @return array<int, MenuItem>
     */
    public function getEntityMenu(string $menuId, string $current, $entity): array
    {
        $menu = [];

        foreach ($this->menuProviders as $provider) {
            if ($provider->supports($menuId, $entity)) {
                $menu = $provider->getMenu($menu, $current, $entity);
            }
        }

        return $menu;
    }
}
