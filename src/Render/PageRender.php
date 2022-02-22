<?php

namespace Softspring\CmsBundle\Render;

use Softspring\CmsBundle\Entity\AbstractModule;
use Softspring\CmsBundle\Model\PageInterface;
use Softspring\CmsBundle\Render\Module\ModuleRenderInterface;
use Twig\Environment;

class PageRender
{
    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var ModuleRenderInterface[]
     */
    protected $moduleRenders;

    protected array $dynamicModules;

    /**
     * @param ModuleRenderInterface[] $moduleRenders
     */
    public function __construct(Environment $twig, array $moduleRenders, array $dynamicModules)
    {
        $this->twig = $twig;
        $this->moduleRenders = $moduleRenders;
        $this->dynamicModules = $dynamicModules;
    }

    public function render(PageInterface $page): string
    {
        if (null !== $page->getDynamicModules()) {
            $content = '';

            foreach ($page->getDynamicModules() as $dynamicModule) {
                $content .= $this->renderModule2($dynamicModule);
            }
        } else {
            $content = implode('', $page->getModules()->map(function (AbstractModule $module) {
                return $this->getModuleRender($module)->render($module);
            })->toArray());
        }

        return $this->twig->render($page->getLayout()->getTemplate(), [
            'content' => $content,
            'page' => $page,
        ]);
    }

    protected function renderModule2(array $module): string
    {
        if ($module['_type'] == 'container') {
            $content = '';

            foreach ($module['modules']['modules'] as $submodule) {
                $content .= '<div class="col">'.$this->renderModule2($submodule).'</div>';
            }

            return "<div class=\"row\">$content</div>";
        }

        return $this->twig->render($this->dynamicModules[$module['_type']]['render_template'], $module);
    }

    protected function getModuleRender(AbstractModule $module): ModuleRenderInterface
    {
        foreach ($this->moduleRenders as $moduleRender) {
            if ($moduleRender->supports($module)) {
                return $moduleRender;
            }
        }

        throw new \RuntimeException(sprintf('There is not any render class supporting "%s" module rendering', get_class($module)));
    }
}
