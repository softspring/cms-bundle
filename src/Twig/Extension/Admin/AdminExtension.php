<?php

namespace Softspring\CmsBundle\Twig\Extension\Admin;

use Softspring\CmsBundle\Admin\Menu\MenuProvider;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AdminExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        protected RouterInterface $router,
        protected ContentManagerInterface $contentManager,
        protected MenuProvider $menuProvider,
        protected bool $recompileEnabled,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'sfs_cms_admin_content_recompile_enabled' => $this->recompileEnabled,
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sfs_cms_admin_content_url', [$this, 'getContentUrl']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_admin_content_url', [$this, 'getContentUrl']),
            new TwigFunction('sfs_cms_admin_content_menu', [$this->menuProvider, 'getContentMenu']),
        ];
    }

    public function getContentUrl(ContentInterface $content, string $action = 'details'): string
    {
        $contentType = $this->contentManager->getType($content);

        return $this->router->generate(sprintf('sfs_cms_admin_content_%s_%s', $contentType, $action), ['content' => $content]);
    }
}
