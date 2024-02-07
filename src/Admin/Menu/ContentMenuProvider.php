<?php

namespace Softspring\CmsBundle\Admin\Menu;

use Softspring\CmsBundle\Config\Exception\InvalidContentException;

class ContentMenuProvider extends AbstractContentMenuProvider
{
    public static function getPriority(): int
    {
        return 255; // should be the first one to be executed
    }

    /**
     * @throws InvalidContentException
     */
    public function getMenu(array $menu, ?string $currentSelection = null, array $context = []): array
    {
        [$content, $contentType, $contentConfig] = $this->getContent($context);

        $menu[] = $this->getMenuItem('details', $currentSelection, $content, $contentType, $contentConfig, 'read');
        $menu[] = $this->getMenuItem('content', $currentSelection, $content, $contentType, $contentConfig, 'version_create');
        $menu[] = $this->getMenuItem('preview', $currentSelection, $content, $contentType, $contentConfig);
        $menu[] = $this->getMenuItem('seo', $currentSelection, $content, $contentType, $contentConfig);
        $menu[] = new MenuItem('routes', $this->translator->trans("admin_{$contentType}.tabs_menu.routes", [], 'sfs_cms_contents'));
        $menu[] = $this->getMenuItem('versions', $currentSelection, $content, $contentType, $contentConfig, 'version_list');
        $menu[] = $this->getMenuItem('update', $currentSelection, $content, $contentType, $contentConfig);
        $menu[] = new MenuItem('permissions', $this->translator->trans("admin_{$contentType}.tabs_menu.permissions", [], 'sfs_cms_contents'));
        $menu[] = $this->getMenuItem('delete', $currentSelection, $content, $contentType, $contentConfig);

        return $menu;
    }
}
