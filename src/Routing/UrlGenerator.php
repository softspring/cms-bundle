<?php

namespace Softspring\CmsBundle\Routing;

use Exception;
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
     * @throws Exception
     */
    public function getUrl($routeOrName, ?string $locale = null, $site = null, array $routeParams = [], bool $onlyChecking = false): string
    {
        $route = $routeOrName instanceof RouteInterface ? $routeOrName : $this->getRoute($routeOrName);

        if (!$route instanceof RouteInterface) {
            if ($onlyChecking) {
                throw new RouteNotFoundException();
            }

            return '#';
        }

        if ($route->getContent() && !$route->getContent()->getPublishedVersion()) {
            return '#not-published';
        }

        $queryString = [] === $routeParams ? '' : '?'.http_build_query($routeParams);

        return $this->getSiteSchemeAndHost($route, $locale, $site).$this->getSiteOrLocalePath($route, $locale, $site).'/'.$this->getRoutePath($route, $locale, $site).$queryString;
    }

    /**
     * @param string|RouteInterface $routeOrName
     *
     * @throws Exception
     */
    public function getPath($routeOrName, ?string $locale = null, $site = null, array $routeParams = [], bool $onlyChecking = false): string
    {
        $route = $routeOrName instanceof RouteInterface ? $routeOrName : $this->getRoute($routeOrName);

        if (!$route instanceof RouteInterface) {
            if ($onlyChecking) {
                throw new RouteNotFoundException();
            }

            return '#';
        }

        if ($route->getContent() && !$route->getContent()->getPublishedVersion()) {
            return '#';
        }

        $queryString = [] === $routeParams ? '' : '?'.http_build_query($routeParams);

        return $this->getSiteOrLocalePath($route, $locale, $site).'/'.$this->getRoutePath($route, $locale, $site).$queryString;
    }

    /**
     * @throws Exception
     */
    public function getUrlFixed(RoutePathInterface $routePath, $site = null): string
    {
        $route = $routePath->getRoute();
        $locale = $routePath->getLocale();

        return $this->getSiteSchemeAndHost($route, $locale, $site).$this->getSiteOrLocalePath($route, $locale, $site).'/'.$routePath->getCompiledPath();
    }

    /**
     * @throws Exception
     */
    public function getPathFixed(RoutePathInterface $routePath, $site = null): string
    {
        $route = $routePath->getRoute();
        $locale = $routePath->getLocale();

        return $this->getSiteOrLocalePath($route, $locale, $site).'/'.$routePath->getCompiledPath();
    }

    /**
     * @param string|RouteInterface|array $routeOrName
     *
     * @throws Exception
     */
    public function getRouteAttributes($routeOrName): string
    {
        $route = $routeOrName instanceof RouteInterface ? $routeOrName : (is_array($routeOrName) ? $this->getRoute($routeOrName['route_name']) : $this->getRoute($routeOrName, true));

        if (!$route instanceof RouteInterface) {
            return '';
        }

        return $route->getLinkAttrs();
    }

    protected function getRoutePath(RouteInterface $route, ?string $locale = null, $site = null): string
    {
        $locale = $locale ?: $this->requestStack->getCurrentRequest()->getLocale();

        /* @var RoutePathInterface $path */
        if ('' !== $locale && '0' !== $locale) {
            $path = $route->getPaths()->filter(fn (RoutePathInterface $routePath): bool => $routePath->getLocale() == $locale)->first();
        } else {
            $path = null;
        }

        if (!$path) {
            throw new RouteNotFoundException(sprintf('Route path for route "%s" and locale "%s" not found', $route->getId(), $locale));
        }

        return $path->getCompiledPath();
    }

    protected function getRoute($routeName, bool $silence = false): ?RouteInterface
    {
        if (!$routeName) {
            $this->cmsLogger && $this->cmsLogger->warning('Empty route');

            return null;
        }

        $route = $this->routeManager->getRepository()->findOneById($routeName);

        if (!$route && !$silence) {
            $this->cmsLogger && $this->cmsLogger->warning(sprintf('Route %s not found', $routeName));
        }

        return $route;
    }

    protected function isPreview(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request && $request->attributes->has('_cms_preview');
    }

    protected function getSiteSchemeAndHost(RouteInterface $route, ?string $locale, $site = null): string
    {
        $locale = $locale ?: $this->requestStack->getCurrentRequest()->getLocale();
        $site = $this->getSite($site, $this->requestStack->getCurrentRequest());

        if (!$route->hasSite("$site")) {
            $site = $route->getSites()->first();
        }

        if ($site instanceof SiteInterface) {
            // todo, could we use SiteInterface::getCanonicalHost() and SiteInterface::getCanonicalScheme()?
            foreach ($site->getConfig()['hosts'] as $hostConfig) {
                if ($hostConfig['canonical'] && (!$hostConfig['locale'] || $hostConfig['locale'] === $locale)) {
                    $scheme = $hostConfig['scheme'] ?: $this->requestStack->getCurrentRequest()->getScheme();
                    $host = $hostConfig['domain'];
                    $port = $hostConfig['port'] ?? null;

                    return "$scheme://$host".($port ? ":$port" : '');
                }
            }
        }

        return $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
    }

    protected function getSiteOrLocalePath(RouteInterface $route, ?string $locale, $site = null): string
    {
        $locale = $locale ?: $this->requestStack->getCurrentRequest()->getLocale();
        $site = $this->getSite($site, $this->requestStack->getCurrentRequest());

        if (!$route->hasSite("$site")) {
            $site = $route->getSites()->first();
        }

        if ('path' == $this->siteConfig['identification']) {
            throw new Exception('Not yet implemented');
        }

        if ($site instanceof SiteInterface) {
            foreach ($site->getConfig()['paths'] as $pathConfig) {
                if (!empty($pathConfig['locale']) && $pathConfig['locale'] === $locale) {
                    return '/'.trim($pathConfig['path'], '/');
                }
            }
        }

        return '';
    }

    protected function getSite($site, ?Request $request): ?SiteInterface
    {
        if ($site instanceof SiteInterface) {
            return $site;
        }

        if (is_string($site) && $site = $this->cmsConfig->getSite($site)) {
            return $site;
        }

        if ($request && $request->attributes->has('_sfs_cms_site')) {
            return $request->attributes->get('_sfs_cms_site');
        }

        return null;
    }
}
