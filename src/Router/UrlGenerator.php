<?php

namespace Softspring\CmsBundle\Router;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\Route;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class UrlGenerator
{
    protected RequestStack $requestStack;
    protected RouteManagerInterface $routeManager;
    protected CmsConfig $cmsConfig;
    protected array $siteConfig;
    protected ?LoggerInterface $cmsLogger;

    public function __construct(RequestStack $requestStack, RouteManagerInterface $routeManager, CmsConfig $cmsConfig, array $siteConfig, ?LoggerInterface $cmsLogger)
    {
        $this->requestStack = $requestStack;
        $this->routeManager = $routeManager;
        $this->cmsConfig = $cmsConfig;
        $this->siteConfig = $siteConfig;
        $this->cmsLogger = $cmsLogger;
    }

    /**
     * @param string|RouteInterface $routeOrName
     *
     * @throws \Exception
     */
    public function getUrl($routeOrName, ?string $locale = null): string
    {
        if ($this->isPreview()) {
            return 'javascript:confirm(\'Esto es una previsualización!\')';
        }

        $route = $routeOrName instanceof RouteInterface ? $routeOrName : $this->getRoute($routeOrName);

        if (!$route) {
            return '#';
        }

        return $this->getSiteSchemeAndHost($route, $locale).$this->getSiteOrLocalePath($route, $locale).'/'.$this->getRoutePath($route, $locale);
    }

    /**
     * @param string|RouteInterface $routeOrName
     *
     * @throws \Exception
     */
    public function getPath($routeOrName, ?string $locale = null): string
    {
        if ($this->isPreview()) {
            return 'javascript:confirm(\'Esto es una previsualización!\')';
        }

        $route = $routeOrName instanceof RouteInterface ? $routeOrName : $this->getRoute($routeOrName);

        if (!$route) {
            return '#';
        }

        return $this->getSiteOrLocalePath($route, $locale).'/'.$this->getRoutePath($route, $locale);
    }

    /**
     * @throws \Exception
     */
    public function getUrlFixed(RoutePathInterface $routePath): string
    {
        if ($this->isPreview()) {
            return 'javascript:confirm(\'Esto es una previsualización!\')';
        }

        $route = $routePath->getRoute();
        $locale = $routePath->getLocale();

        return $this->getSiteSchemeAndHost($route, $locale).$this->getSiteOrLocalePath($route, $locale).'/'.$routePath->getPath();
    }

    /**
     * @throws \Exception
     */
    public function getPathFixed(RoutePathInterface $routePath): string
    {
        if ($this->isPreview()) {
            return 'javascript:confirm(\'Esto es una previsualización!\')';
        }

        $route = $routePath->getRoute();
        $locale = $routePath->getLocale();

        return $this->getSiteOrLocalePath($route, $locale).'/'.$routePath->getPath();
    }

    /**
     * @param string|RouteInterface $routeOrName
     *
     * @throws \Exception
     */
    public function getRouteAttributes($routeOrName): string
    {
        $route = $routeOrName instanceof RouteInterface ? $routeOrName : $this->getRoute($routeOrName);

        if (!$route) {
            return '';
        }

        $attrs = [];

        if (RouteInterface::TYPE_CONTENT === $route->getType() && $route->getContent()) {
            $seo = $route->getContent()->getSeo();
            if (isset($seo['noIndex'])) {
                $attrs['rel'][] = $seo['noIndex'] ? 'noindex' : 'index';
            }
            if (isset($seo['noFollow'])) {
                $attrs['rel'][] = $seo['noFollow'] ? 'nofollow' : 'follow';
            }
        }

        foreach ($attrs as $attr => $value) {
            if ('rel' === $attr && is_array($value)) {
                $value = implode(',', $value);
            }

            $attrs[] = $attr.'="'.htmlentities($value).'"';
            unset($attrs[$attr]);
        }

        return implode(' ', $attrs);
    }

    protected function getRoutePath(Route $route, ?string $locale = null): string
    {
        $locale = $locale ?: $this->requestStack->getCurrentRequest()->getLocale();

        /* @var RoutePathInterface $path */
        if ($locale) {
            $path = $route->getPaths()->filter(fn (RoutePathInterface $routePath) => $routePath->getLocale() == $locale)->first();
        }

        $path = $path ?: $route->getPaths()->first();

        return $path->getPath();
    }

    protected function getRoute($routeName): ?RouteInterface
    {
        if (!$routeName) {
            $this->cmsLogger && $this->cmsLogger->error('Empty route');

            return null;
        }

        $route = $this->routeManager->getRepository()->findOneById($routeName);

        if (!$route) {
            $this->cmsLogger && $this->cmsLogger->error(sprintf('Route %s not found', $routeName));
        }

        return $route;
    }

    protected function isPreview(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->attributes->has('_cms_preview');
    }

    protected function getSiteSchemeAndHost(RouteInterface $route, ?string $locale): string
    {
        $locale = $locale ?: $this->requestStack->getCurrentRequest()->getLocale();
        $siteConfig = $this->cmsConfig->getSite($route->getSite());

        foreach ($siteConfig['hosts'] as $hostConfig) {
            if ($hostConfig['canonical'] && (!$hostConfig['locale'] || $hostConfig['locale'] === $locale)) {
                $scheme = $hostConfig['scheme'] ?: $this->requestStack->getCurrentRequest()->getScheme();
                $host = $hostConfig['domain'];

                return "$scheme://$host";
            }
        }

        return $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
    }

    protected function getSiteOrLocalePath(RouteInterface $route, ?string $locale): string
    {
        $locale = $locale ?: $this->requestStack->getCurrentRequest()->getLocale();
        $siteConfig = $this->cmsConfig->getSite($route->getSite());

        if ('path' == $this->siteConfig['identification']) {
            throw new \Exception('Not yet implemented');
        }

        foreach ($siteConfig['paths'] as $pathConfig) {
            if (!empty($pathConfig['locale']) && $pathConfig['locale'] === $locale) {
                return '/'.trim($pathConfig['path'], '/');
            }
        }

        return '';
    }
}
