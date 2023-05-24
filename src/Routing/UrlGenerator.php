<?php

namespace Softspring\CmsBundle\Routing;

use Psr\Log\LoggerInterface;
use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\RouteManagerInterface;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

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
    public function getUrl($routeOrName, string $locale = null, $site = null, array $routeParams = [], bool $onlyChecking = false): string
    {
        if ($this->isPreview()) {
            return 'javascript:confirm(\'Esto es una previsualización!\')';
        }

        $route = $routeOrName instanceof RouteInterface ? $routeOrName : $this->getRoute($routeOrName);

        if (!$route) {
            if ($onlyChecking) {
                throw new RouteNotFoundException();
            }

            return '#';
        }

        $queryString = !empty($routeParams) ? '?'.http_build_query($routeParams) : '';

        return $this->getSiteSchemeAndHost($route, $locale, $site).$this->getSiteOrLocalePath($route, $locale, $site).'/'.$this->getRoutePath($route, $locale, $site).$queryString;
    }

    /**
     * @param string|RouteInterface $routeOrName
     *
     * @throws \Exception
     */
    public function getPath($routeOrName, string $locale = null, $site = null, array $routeParams = [], bool $onlyChecking = false): string
    {
        if ($this->isPreview()) {
            return 'javascript:confirm(\'Esto es una previsualización!\')';
        }

        $route = $routeOrName instanceof RouteInterface ? $routeOrName : $this->getRoute($routeOrName);

        if (!$route) {
            if ($onlyChecking) {
                throw new RouteNotFoundException();
            }

            return '#';
        }

        $queryString = !empty($routeParams) ? '?'.http_build_query($routeParams) : '';

        return $this->getSiteOrLocalePath($route, $locale, $site).'/'.$this->getRoutePath($route, $locale, $site).$queryString;
    }

    /**
     * @throws \Exception
     */
    public function getUrlFixed(RoutePathInterface $routePath, $site = null): string
    {
        if ($this->isPreview()) {
            return 'javascript:confirm(\'Esto es una previsualización!\')';
        }

        $route = $routePath->getRoute();
        $locale = $routePath->getLocale();

        return $this->getSiteSchemeAndHost($route, $locale, $site).$this->getSiteOrLocalePath($route, $locale, $site).'/'.$routePath->getCompiledPath();
    }

    /**
     * @throws \Exception
     */
    public function getPathFixed(RoutePathInterface $routePath, $site = null): string
    {
        if ($this->isPreview()) {
            return 'javascript:confirm(\'Esto es una previsualización!\')';
        }

        $route = $routePath->getRoute();
        $locale = $routePath->getLocale();

        return $this->getSiteOrLocalePath($route, $locale, $site).'/'.$routePath->getCompiledPath();
    }

    /**
     * @param string|RouteInterface|array $routeOrName
     *
     * @throws \Exception
     */
    public function getRouteAttributes($routeOrName): string
    {
        $route = $routeOrName instanceof RouteInterface ? $routeOrName : (is_array($routeOrName) ? $this->getRoute($routeOrName['route_name']) : $this->getRoute($routeOrName));

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

    protected function getRoutePath(RouteInterface $route, string $locale = null, $site = null): string
    {
        $locale = $locale ?: $this->requestStack->getCurrentRequest()->getLocale();

        /* @var RoutePathInterface $path */
        if ($locale) {
            $path = $route->getPaths()->filter(fn (RoutePathInterface $routePath) => $routePath->getLocale() == $locale)->first();
        } else {
            $path = null;
        }

        $path = $path ?: $route->getPaths()->first();

        return $path->getCompiledPath();
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

    protected function getSiteSchemeAndHost(RouteInterface $route, ?string $locale, $site = null): string
    {
        $locale = $locale ?: $this->requestStack->getCurrentRequest()->getLocale();
        $site = $this->getSite($site, $this->requestStack->getCurrentRequest());

        if (!$route->hasSite("$site")) {
            throw new RouteNotFoundException();
        }

        foreach ($site->getConfig()['hosts'] as $hostConfig) {
            if ($hostConfig['canonical'] && (!$hostConfig['locale'] || $hostConfig['locale'] === $locale)) {
                $scheme = $hostConfig['scheme'] ?: $this->requestStack->getCurrentRequest()->getScheme();
                $host = $hostConfig['domain'];

                return "$scheme://$host";
            }
        }

        return $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
    }

    protected function getSiteOrLocalePath(RouteInterface $route, ?string $locale, $site = null): string
    {
        $locale = $locale ?: $this->requestStack->getCurrentRequest()->getLocale();
        $site = $this->getSite($site, $this->requestStack->getCurrentRequest());

        if (!$route->hasSite("$site")) {
            throw new RouteNotFoundException();
        }

        if ('path' == $this->siteConfig['identification']) {
            throw new \Exception('Not yet implemented');
        }

        foreach ($site->getConfig()['paths'] as $pathConfig) {
            if (!empty($pathConfig['locale']) && $pathConfig['locale'] === $locale) {
                return '/'.trim($pathConfig['path'], '/');
            }
        }

        return '';
    }

    protected function getSite($site, ?Request $request): ?SiteInterface
    {
        if ($site instanceof SiteInterface) {
            return $site;
        }

        if (is_string($site)) {
            $this->cmsConfig->getSite($site);
        }

        if ($request && $request->attributes->has('_sfs_cms_site')) {
            return $request->attributes->get('_sfs_cms_site');
        }

        return null;
    }
}
