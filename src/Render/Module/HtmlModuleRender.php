<?php

namespace Softspring\CmsBundle\Render\Module;

use Softspring\CmsBundle\Entity\AbstractModule;
use Softspring\CmsBundle\Entity\Modules\HtmlModule;
use Twig\Environment;

class HtmlModuleRender implements ModuleRenderInterface
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param HtmlModule $module
     *
     * @return string
     */
    public function render(AbstractModule $module): string
    {
        if (!$this->supports($module)) {
            throw new \RuntimeException(sprintf('This renderer class does not supports "%s" module rendering', get_class($module)));
        }

        return $this->twig->render('@SfsCms/modules/html/render.html.twig', [
            'module' => $module,
        ]);
    }

    /**
     * @param AbstractModule $module
     *
     * @return bool
     */
    public function supports(AbstractModule $module): bool
    {
        return $module instanceof HtmlModule;
    }
}