<?php

namespace Softspring\CmsBundle\Twig\Extension\Admin;

use Softspring\CmsBundle\Admin\Menu\MenuManager;
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
        protected MenuManager $menuManager,
        protected bool $contentRecompileEnabled,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'sfs_cms_admin_content_recompile_enabled' => $this->contentRecompileEnabled,
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
            new TwigFunction('sfs_cms_admin_content_menu', [$this, 'getContentMenu']),
        ];
    }

    public function getContentUrl(ContentInterface $content, string $action = 'details'): string
    {
        $contentType = $this->contentManager->getType($content);

        return $this->router->generate(sprintf('sfs_cms_admin_content_%s_%s', $contentType, $action), ['content' => $content]);
    }

    public function getContentMenu(string $current, ContentInterface $content): array
    {
        return $this->menuManager->getEntityMenu('content', $current, $content);
    }
}
