<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Render\BlockRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BlockExtension extends AbstractExtension
{
    protected BlockManagerInterface $blockManager;
    protected BlockRenderer $blockRenderer;

    public function __construct(BlockManagerInterface $blockManager, BlockRenderer $blockRenderer)
    {
        $this->blockManager = $blockManager;
        $this->blockRenderer = $blockRenderer;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_block', [$this->blockRenderer, 'renderBlock'], ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_block_by_type', [$this->blockRenderer, 'renderBlockByType'], ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_block_by_id', [$this, 'renderBlockById'], ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_block_find', [$this, 'findOneBy'], ['is_safe' => ['html']]),
        ];
    }

    public function renderBlockById($id, ?string $locale = null): string
    {
        $block = $this->findOneBy($id);

        return $block ? $this->blockRenderer->renderBlock($block, $locale) : "<!-- block $id not found -->";
    }

    public function findOneBy($criteria, array $orderBy = []): ?BlockInterface
    {
        if (is_string($criteria)) {
            $criteria = ['id' => $criteria];
        }

        if (!is_array($criteria)) {
            throw new \Exception('Invalid criteria');
        }

        return $this->blockManager->getRepository()->findOneBy($criteria, $orderBy);
    }
}
