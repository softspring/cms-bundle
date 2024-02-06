<?php

namespace Softspring\CmsBundle\Admin;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentMenu
{
    public function __construct(
        protected CmsConfig                     $cmsConfig,
        protected ContentManagerInterface       $contentManager,
        protected RouterInterface               $router,
        protected TranslatorInterface           $translator,
        protected AuthorizationCheckerInterface $authorizationChecker,
    )
    {
    }

    public function getAdminContentMenu(string $current, ContentInterface $content): array
    {
        $menu = [];

        $contentType = $this->contentManager->getType($content);
        $contentConfig = $this->cmsConfig->getContent($contentType);

        $menu[] = $this->getMenuItem('details', $current, $content, $contentType, $contentConfig);
        $menu[] = $this->getMenuItem('content', $current, $content, $contentType, $contentConfig);
        $menu[] = $this->getMenuItem('preview', $current, $content, $contentType, $contentConfig);
        $menu[] = $this->getMenuItem('seo', $current, $content, $contentType, $contentConfig);
        $menu[] = [
            'id' => 'routes',
            'text' => $this->translator->trans("admin_{$contentType}.tabs_menu.routes", [], 'sfs_cms_contents'),
            'url' => null,
            'active' => false,
            'disabled' => true,
        ];

        if (sizeof($content->getLocales()) > 1) {
            $menu[] = $this->getMenuItem('translations', $current, $content, $contentType, $contentConfig, 'versions_translations');
        }

        $menu[] = $this->getMenuItem('versions', $current, $content, $contentType, $contentConfig);
        $menu[] = $this->getMenuItem('update', $current, $content, $contentType, $contentConfig);
        // $menu[] = [
        //     'id' => 'permissions',
        //     'text' => $this->translator->trans("admin_{$contentType}.tabs_menu.permissions", [], 'sfs_cms_contents'),
        //     'url' => null,
        //     'active' => false,
        //     'disabled' => true,
        // ];
        $menu[] = $this->getMenuItem('delete', $current, $content, $contentType, $contentConfig);

        return $menu;
    }

    protected function getMenuItem(string $name, string $current, ContentInterface $content, string $contentType, array $contentConfig, ?string $configKey = null): array
    {
        $isGranted = $contentConfig['admin'][$configKey ?? $name]['is_granted'] ?? null;

        return [
            'id' => $name,
            'text' => $this->translator->trans("admin_{$contentType}.tabs_menu.$name", [], 'sfs_cms_contents'),
            'url' => $this->router->generate("sfs_cms_admin_content_{$contentType}_{$name}", ['content' => $content]),
            'active' => $current == $name,
            'disabled' => $isGranted && !$this->authorizationChecker->isGranted($isGranted, $content),
        ];
    }
}
