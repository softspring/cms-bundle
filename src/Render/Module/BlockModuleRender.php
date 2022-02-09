<?php

namespace Softspring\CmsBundle\Render\Module;

use Softspring\CmsBundle\Entity\AbstractModule;
use Softspring\CmsBundle\Entity\Modules\BlockModule;
use Softspring\CmsBundle\Render\RenderBlock;

class BlockModuleRender implements ModuleRenderInterface
{
    /**
     * @var RenderBlock
     */
    protected $renderBlock;

    public function __construct(RenderBlock $renderBlock)
    {
        $this->renderBlock = $renderBlock;
    }

    /**
     * @param BlockModule $module
     */
    public function render(AbstractModule $module): string
    {
        if (!$this->supports($module)) {
            throw new \RuntimeException(sprintf('This renderer class does not supports "%s" module rendering', get_class($module)));
        }

        return $this->renderBlock->render($module->getBlock());
    }

    public function supports(AbstractModule $module): bool
    {
        return $module instanceof BlockModule;
    }
}
