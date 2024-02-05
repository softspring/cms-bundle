<?php

namespace Softspring\CmsBundle\Twig\Extension;

use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
            new TwigFunction('sfs_cms_link_attr', [$this, 'generateLinkAttributes'], ['is_safe' => ['html']]),
            new TwigFunction('sfs_cms_url', [$this, 'generateUrl']),
            new TwigFunction('sfs_cms_path', [$this, 'generatePath']),
            new TwigFunction('sfs_cms_route_path_url', [$this->urlGenerator, 'getUrlFixed']), // TODO REVIEW THIS, check if it works with symfony native routes
            new TwigFunction('sfs_cms_route_path_path', [$this->urlGenerator, 'getPathFixed']), // TODO REVIEW THIS, check if it works with symfony native routes
            new TwigFunction('sfs_cms_route_attr', [$this->urlGenerator, 'getRouteAttributes']), // TODO REVIEW THIS, check if it works with symfony native routes
        ];
    }

    public function generateLinkAttributes(array $linkData, ?string $locale = null, $site = null): string
    {
        $attributes = [];
        $attributesString = '';

        switch ($linkData['type']) {
            case 'anchor':
                $attributes['href'] = '#'.ltrim($linkData['anchor'], '#');
                break;

            case 'route':
                $attributes['href'] = $this->generateUrl($linkData, $locale, $site);
                $attributesString .= $this->urlGenerator->getRouteAttributes($linkData);
                break;

            case 'url':
                $attributes['href'] = $linkData['url'];
                break;
        }

        if ('_self' != $linkData['target']) {
            if ('custom' != $linkData['target']) {
                $attributes['target'] = $linkData['target'];
            } else {
                $attributes['target'] = $linkData['custom_target'];
            }
        }

        foreach ($attributes as $attr => $value) {
            $attributes[] = $attr.'="'.htmlentities($value).'"';
            unset($attributes[$attr]);
        }

        $attributesString && $attributes[] = $attributesString;

        return implode(' ', $attributes);
    }

    public function generatePath($route, ?string $locale = null, $site = null): string
    {
        return $this->generateUrl($route, $locale, $site, UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    public function generateUrl($route, ?string $locale = null, $site = null, int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        if (is_null($route)) {
            return '#';
        }

        if (is_array($route)) {
            if (is_null($route['route_name'])) {
                return '#';
            }

            $params = $route['route_params'] ?? [];

            if ($locale) {
                $params['_locale'] = $this->requestStack->getCurrentRequest()->getLocale(); // TODO CHECK THIS ????
            }

            if ($site) {
                $params['_site'] = $site;
            }

            return $this->router->generate($route['route_name'], $params, $referenceType);
        } elseif ($route instanceof RouteInterface) {
            return $this->router->generate($route->getId(), [], $referenceType);
        }

        $params = [];

        if ($locale) {
            $params['_locale'] = $this->requestStack->getCurrentRequest()->getLocale();
        }

        return $this->router->generate($route, $params, $referenceType);
    }
}
