<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Admin\Menu;

use Softspring\CmsBundle\Model\SiteInterface;

class SiteMenuProvider extends AbstractSiteMenuProvider
{
    public static function getPriority(): int
    {
        return 255;
    }

    public function getMenu(array $menu, ?string $currentSelection = null, ?object $entity = null): array
    {
        if (!$entity instanceof SiteInterface) {
            return $menu;
        }

        $menu[] = $this->getMenuItem('details', (string) $currentSelection, $entity, 'sfs_cms_admin_sites_read');

        return $menu;
    }
}
