<?php

namespace Softspring\CmsBundle\Admin\Menu;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Model\SectionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractSectionMenuProvider implements SectionMenuProviderInterface
{
    public function __construct(
        protected CmsConfig $cmsConfig,
        protected SectionManagerInterface $sectionManager,
        protected RouterInterface $router,
        protected TranslatorInterface $translator,
        protected AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    protected function getMenuItem(string $id, string $current, SectionInterface $section, ?string $isGranted = null): MenuItem
    {
        $text = $this->translator->trans("admin_sections.tabs_menu.$id", [], 'sfs_cms_admin');
        $url = $this->router->generate("sfs_cms_admin_sections_{$id}", ['section' => $section->getId()]);
        $active = $current == $id;
        $disabled = ('#' === $url) || ($isGranted && !$this->authorizationChecker->isGranted($isGranted, $section));

        return new MenuItem($id, $text, $url, $active, $disabled);
    }
}
