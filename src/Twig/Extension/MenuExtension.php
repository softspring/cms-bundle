<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Render\MenuRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MenuExtension extends AbstractExtension
{
    protected MenuRenderer $menuRenderer;

    public function __construct(MenuRenderer $menuRenderer)
    {
        $this->menuRenderer = $menuRenderer;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_menu', [$this->menuRenderer, 'renderMenuByType'], ['is_safe' => ['html']]),
        ];
    }
}
