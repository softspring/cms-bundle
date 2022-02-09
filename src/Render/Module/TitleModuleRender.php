<?php

namespace Softspring\CmsBundle\Render\Module;

use Softspring\CmsBundle\Entity\AbstractModule;
use Softspring\CmsBundle\Entity\Modules\TitleModule;
use Twig\Environment;

class TitleModuleRender implements ModuleRenderInterface
{
    /**
     * @var Environment
     */
    protected $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param TitleModule $module
     */
    public function render(AbstractModule $module): string
    {
        if (!$this->supports($module)) {
            throw new \RuntimeException(sprintf('This renderer class does not supports "%s" module rendering', get_class($module)));
        }

        return $this->twig->render('@SfsCms/modules/title/render.html.twig', [
            'module' => $module,
        ]);
    }

    public function supports(AbstractModule $module): bool
    {
        return $module instanceof TitleModule;
    }
}
