<?php

namespace Softspring\CmsBundle\Admin\Menu;

use Softspring\CmsBundle\Model\ContentInterface;

class MenuProvider
{
    /**
     * @param iterable<int, ContentMenuProviderInterface> $contentMenuProviders
     */
    public function __construct(
        protected iterable $contentMenuProviders
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
}
