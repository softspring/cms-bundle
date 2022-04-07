<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Manager\BlockManagerInterface;
use Softspring\CmsBundle\Model\BlockInterface;
use Softspring\CmsBundle\Model\MultiSiteInterface;
use Softspring\CmsBundle\Model\SchedulableContentInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Render\RenderBlock;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @deprecated
 */
class BlockExtension extends AbstractExtension
{
    protected EntityManagerInterface $em;

    protected RenderBlock $renderer;

    protected ?BlockManagerInterface $blockManager;

    public function __construct(EntityManagerInterface $em, RenderBlock $renderer, ?BlockManagerInterface $blockManager)
    {
        $this->em = $em;
        $this->renderer = $renderer;
        $this->blockManager = $blockManager;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_render_block', [$this, 'renderBlock'], ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_preview_block', [$this, 'previewBlock'], ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_blocks_are_schedulable', [$this, 'blocksAreSchedulable']),
            new TwigFunction('sfs_cms_blocks_are_multisite', [$this, 'blocksAreMultisite']),
        ];
    }

    public function renderBlock(string $key, string $_locale = null, ?SiteInterface $site = null, bool $allowMultiple = false): string
    {
        $repo = $this->blockManager->getRepository();

        /** @var Collection|BlockInterface[] $blocks */
        $blocks = $repo->findBy(['key' => $key]);
        $blocks = $blocks instanceof Collection ? $blocks : (new ArrayCollection($blocks));

        if (!$blocks->count()) {
            return '';
        }

        if ($allowMultiple) {
            return implode('', array_map([$this->renderer, 'render'], $blocks->toArray()));
        } else {
            return $this->renderer->render($blocks->first());
        }
    }

    public function previewBlock(string $blockId, string $_locale = null, ?SiteInterface $site = null, bool $allowMultiple = false): string
    {
        $filters = $this->em->getFilters();
        $filterPresent = $filters->has('schedulable');
        $filterEnabled = $filterPresent && $filters->isEnabled('schedulable');

        if ($filterEnabled) {
            $this->em->getFilters()->disable('schedulable');
        }

        $render = $this->renderBlock($blockId, $_locale, $site);

        if ($filterEnabled) {
            $this->em->getFilters()->enable('schedulable');
        }

        return $render;
    }

    public function blocksAreSchedulable(): bool
    {
        if (!$this->blockManager instanceof BlockManagerInterface) {
            return false;
        }

        return $this->blockManager->getEntityClassReflection()->implementsInterface(SchedulableContentInterface::class);
    }

    public function blocksAreMultisite(): bool
    {
        if (!$this->blockManager instanceof BlockManagerInterface) {
            return false;
        }

        return $this->blockManager->getEntityClassReflection()->implementsInterface(MultiSiteInterface::class);
    }
}
