<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Render\BlockRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BlockExtension extends AbstractExtension
{
    protected BlockRenderer $blockRenderer;

    public function __construct(BlockRenderer $blockRenderer)
    {
        $this->blockRenderer = $blockRenderer;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_block', [$this->blockRenderer, 'renderBlock'], ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_block_by_type', [$this->blockRenderer, 'renderBlockByType'], ['is_safe' => ['html']]),
        ];
    }
}
