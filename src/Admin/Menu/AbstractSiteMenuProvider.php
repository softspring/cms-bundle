<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Admin\Menu;

use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractSiteMenuProvider implements MenuProviderInterface
{
    public function __construct(
        protected RouterInterface $router,
        protected TranslatorInterface $translator,
    ) {
    }

    public function supports(string $menuId, object $entity): bool
    {
        return 'site' === $menuId && $entity instanceof SiteInterface;
    }

    protected function getMenuItem(string $id, string $current, SiteInterface $site, string $routeName): MenuItem
    {
        return new MenuItem(
            $id,
            $this->translator->trans("admin_sites.tabs_menu.$id", [], 'sfs_cms_admin'),
            $this->router->generate($routeName, ['site' => $site->getId()]),
            $current === $id,
            false,
        );
    }
}
