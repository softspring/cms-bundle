<?php

namespace Softspring\CmsBundle\Twig\Extension\Admin;

use Softspring\CmsBundle\Admin\ContentMenu;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AdminExtension extends AbstractExtension
{
    public function __construct(protected RouterInterface $router, protected ContentManagerInterface $contentManager, protected ContentMenu $contentMenu)
    {
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
            new TwigFunction('sfs_cms_admin_content_menu', [$this->contentMenu, 'getAdminContentMenu']),
        ];
    }

    public function getContentUrl(ContentInterface $content, string $action = 'details'): string
    {
        $contentType = $this->contentManager->getType($content);

        return $this->router->generate(sprintf('sfs_cms_admin_content_%s_%s', $contentType, $action), ['content' => $content]);
    }
}
