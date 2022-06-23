<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Router\UrlGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouterExtension extends AbstractExtension
{
    protected UrlGenerator $urlGenerator;

    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_url', [$this->urlGenerator, 'getUrl']),
            new TwigFunction('sfs_cms_path', [$this->urlGenerator, 'getPath']),
            new TwigFunction('sfs_cms_url_fixed', [$this->urlGenerator, 'getUrlFixed']),
            new TwigFunction('sfs_cms_path_fixed', [$this->urlGenerator, 'getPathFixed']),
            new TwigFunction('sfs_cms_route_attr', [$this->urlGenerator, 'getRouteAttributes']),
        ];
    }
}
