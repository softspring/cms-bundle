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

    /**
     * @param Environment            $twig
     * @param ModuleRenderInterface[] $moduleRenders
     */
    public function __construct(Environment $twig, array $moduleRenders)
    {
        $this->twig = $twig;
        $this->moduleRenders = $moduleRenders;
    }

    public function render(PageInterface $page): string
    {
        $content = implode('', $page->getModules()->map(function (AbstractModule $module) {
            return $this->getModuleRender($module)->render($module);
        })->toArray());

        return $this->twig->render($page->getLayout()->getTemplate(), [
            'content' => $content,
            'page' => $page,
        ]);
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