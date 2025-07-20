<?php

namespace Softspring\CmsBundle\Admin\Menu;

use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\SectionInterface;

class MenuProvider
{
    /**
     * @param iterable<int, ContentMenuProviderInterface> $contentMenuProviders
     * @param iterable<int, SectionMenuProviderInterface> $sectionMenuProviders
     */
    public function __construct(
        protected iterable $contentMenuProviders,
        protected iterable $sectionMenuProviders,
    ) {
    }

    /**
     * @return array<int, MenuItem>
     */
    public function getContentMenu(string $current, ContentInterface $content): array
    {
        $menu = [];

        foreach ($this->contentMenuProviders as $provider) {
            $menu = $provider->getMenu($menu, $current, ['content' => $content]);
        }

        return $menu;
    }

    public function getSectionMenu(string $current, SectionInterface $section): array
    {
        $menu = [];

        foreach ($this->sectionMenuProviders as $provider) {
            $menu = $provider->getMenu($menu, $current, ['section' => $section]);
        }

        return $menu;
    }
}
