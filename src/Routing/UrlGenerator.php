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
        $site = $this->resolveSite($route, $site);
        $locale = $this->resolveLocale($route, $locale, $site);

        return $this->getSiteSchemeAndHost($site, $locale).$this->getSiteOrLocalePath($site, $locale).'/'.$this->getRoutePath($route, $locale).$queryString;
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
        $site = $this->resolveSite($route, $site);
        $locale = $this->resolveLocale($route, $locale, $site);

        return $this->getSiteOrLocalePath($site, $locale).'/'.$this->getRoutePath($route, $locale).$queryString;
    }

    /**
     * @throws Exception
     */
    public function getUrlFixed(RoutePathInterface $routePath, $site = null): string
    {
        $route = $routePath->getRoute();
        $locale = $routePath->getLocale();
        $site = $this->resolveSite($route, $site);
        $locale = $this->resolveLocale($route, $locale, $site);

        return $this->getSiteSchemeAndHost($site, $locale).$this->getSiteOrLocalePath($site, $locale).'/'.$routePath->getCompiledPath();
    }

    /**
     * @throws Exception
     */
    public function getPathFixed(RoutePathInterface $routePath, $site = null): string
    {
        $route = $routePath->getRoute();
        $locale = $routePath->getLocale();
        $site = $this->resolveSite($route, $site);
        $locale = $this->resolveLocale($route, $locale, $site);

        return $this->getSiteOrLocalePath($site, $locale).'/'.$routePath->getCompiledPath();
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

    protected function getRoutePath(RouteInterface $route, ?string $locale): string
    {
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

    protected function getSiteSchemeAndHost(?SiteInterface $site, ?string $locale): string
    {
        if ($site instanceof SiteInterface) {
            // todo, could we use SiteInterface::getCanonicalHost() and SiteInterface::getCanonicalScheme()?
            foreach ($site->getConfig()['hosts'] as $hostConfig) {
                if ($hostConfig['canonical'] && (!$hostConfig['locale'] || $hostConfig['locale'] === $locale)) {
                    $scheme = $hostConfig['scheme'] ?: ($this->getCurrentRequest()?->getScheme() ?? 'https');
                    $host = $hostConfig['domain'];
                    $port = $hostConfig['port'] ?? null;

                    return "$scheme://$host".($port ? ":$port" : '');
                }
            }
        }

        if (($request = $this->getCurrentRequest()) instanceof Request) {
            return $request->getSchemeAndHttpHost();
        }

        throw new Exception('Can not generate an absolute URL without a site host or a current request');
    }

    protected function getSiteOrLocalePath(?SiteInterface $site, ?string $locale): string
    {
        if ($site instanceof SiteInterface) {
            foreach ($site->getConfig()['paths'] as $pathConfig) {
                if (!empty($pathConfig['locale']) && $pathConfig['locale'] === $locale) {
                    return '/'.trim($pathConfig['path'], '/');
                }
            }
        }

        return '';
    }

    protected function resolveSite(RouteInterface $route, $site = null): ?SiteInterface
    {
        $site = $this->getSite($site, $this->getCurrentRequest());

        if ($site instanceof SiteInterface && (0 === $route->getSites()->count() || $route->hasSite("$site"))) {
            return $site;
        }

        $routeSite = $route->getSites()->first();

        return $routeSite instanceof SiteInterface ? $routeSite : $site;
    }

    protected function resolveLocale(RouteInterface $route, ?string $locale, ?SiteInterface $site): ?string
    {
        if (null !== $locale && '' !== $locale && '0' !== $locale) {
            return $locale;
        }

        if ($requestLocale = $this->getCurrentRequest()?->getLocale()) {
            return $requestLocale;
        }

        if ($site instanceof SiteInterface && !empty($site->getConfig()['default_locale'])) {
            return $site->getConfig()['default_locale'];
        }

        $routePath = $route->getPaths()->first();

        return $routePath instanceof RoutePathInterface ? $routePath->getLocale() : null;
    }

    protected function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
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
