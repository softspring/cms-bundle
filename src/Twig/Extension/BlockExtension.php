<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Exception;
use Softspring\CmsBundle\Helper\BlogArticleHelper;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Render\BlockRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BlockExtension extends AbstractExtension
{
    protected BlockManagerInterface $blockManager;
    protected BlockRenderer $blockRenderer;
    protected BlogArticleHelper $blogArticleHelper;

    public function __construct(BlockManagerInterface $blockManager, BlockRenderer $blockRenderer, BlogArticleHelper $blogArticleHelper)
    {
        $this->blockManager = $blockManager;
        $this->blockRenderer = $blockRenderer;
        $this->blogArticleHelper = $blogArticleHelper;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_block', $this->blockRenderer->renderBlock(...), ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_block_by_type', $this->blockRenderer->renderBlockByType(...), ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_block_by_id', $this->renderBlockById(...), ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_block_find', $this->findOneBy(...), ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_related_blog_articles', $this->blogArticleHelper->getRelatedArticles(...)),
        ];
    }

    public function renderBlockById($id, ?string $locale = null): string
    {
        $block = $this->findOneBy($id);

        return $block instanceof BlockInterface ? $this->blockRenderer->renderBlock($block, $locale) : "<!-- block $id not found -->";
    }

    public function findOneBy($criteria, array $orderBy = []): ?BlockInterface
    {
        if (is_string($criteria)) {
            $criteria = ['id' => $criteria];
        }

        if (!is_array($criteria)) {
            throw new Exception('Invalid criteria');
        }

        return $this->blockManager->getRepository()->findOneBy($criteria, $orderBy);
    }
}
