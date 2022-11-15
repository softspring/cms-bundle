<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Router\UrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouterExtension extends AbstractExtension
{
    protected UrlGenerator $urlGenerator;
    protected RouterInterface $router;
    protected RequestStack $requestStack;

    public function __construct(UrlGenerator $urlGenerator, RouterInterface $router, RequestStack $requestStack)
    {
        $this->urlGenerator = $urlGenerator;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sfs_cms_url', [$this, 'generateUrl']),
            new TwigFunction('sfs_cms_route_path_url', [$this->urlGenerator, 'getUrlFixed']), // TODO REVIEW THIS, check if it works with symfony native routes
            new TwigFunction('sfs_cms_route_path_path', [$this->urlGenerator, 'getPathFixed']), // TODO REVIEW THIS, check if it works with symfony native routes
            new TwigFunction('sfs_cms_route_attr', [$this->urlGenerator, 'getRouteAttributes']), // TODO REVIEW THIS, check if it works with symfony native routes
        ];
    }

    public function generateUrl($route, ?string $locale = null): string
    {
        if (is_array($route)) {
            $params = $route['route_params'] ?? [];

            if ($locale) {
                $params['_locale'] = $this->requestStack->getCurrentRequest()->getLocale();
            }

            return $this->router->generate($route['route_name'], $params);
        } else if ($route instanceof RouteInterface) {
            return $this->router->generate($route->getId());
        }

        $params = [];

        if ($locale) {
            $params['_locale'] = $this->requestStack->getCurrentRequest()->getLocale();
        }

        return $this->router->generate($route, $params);
    }
}
