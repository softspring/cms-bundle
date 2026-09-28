<?php

namespace Softspring\CmsBundle\Helper;

use Exception;
use Softspring\CmsBundle\Exception\SiteHasNotACanonicalHostException;
use Softspring\CmsBundle\Model\RouteInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Routing\SiteResolver;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Softspring\CmsBundle\Routing\UrlMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class RoutingHelper
{
    public function __construct(
        protected RouterInterface $router,
        protected UrlGenerator $urlGenerator,
        protected SiteResolver $siteResolver,
        protected UrlMatcher $urlMatcher,
        protected RequestStack $requestStack,
    ) {
    }

    /**
     * Generates the alternate URLs for a route path in all sites and languages.
     */
    public function generateRoutePathAlternates(RoutePathInterface $path, SiteInterface $site, bool $localeAlternates = true, bool $siteAlternates = true, bool $addHrefLang = true): array
    {
        $alternates = $localeAlternates ? $this->generateRoutePathAlternatesForSite($site, $path, $addHrefLang) : [];

        if (!$siteAlternates) {
            return $alternates;
        }

        foreach ($path->getRoute()->getSites()->filter(fn ($as): bool => $as !== $site) as $alternateSite) {
            $alternates = array_merge($alternates, $this->generateRoutePathAlternatesForSite($alternateSite, $path, $addHrefLang));
        }

        return $alternates;
    }

    /**
     * Generates the alternate URLs for a route path in a site.
     */
    public function generateRoutePathAlternatesForSite(SiteInterface $site, RoutePathInterface $path, bool $addHrefLang = true): array
    {
        $alternates = [];

        foreach ($path->getRoute()->getPaths() as $alternatePath) {
            if (!in_array($alternatePath->getLocale(), $site->getConfig()['locales'])) {
                continue;
            }

            $alternates[] = [
                '@rel' => 'alternate',
                '@href' => $this->urlGenerator->getUrlFixed($alternatePath, $site),
            ] + ($addHrefLang ? ['@hreflang' => $site->getGeoHrefLangForLocale($alternatePath->getLocale())] : []);
        }

        return $alternates;
    }

    public function resolveRequestFromUrl(string $url): ?Request
    {
        try {
            $request = Request::create($url);
            [$siteId, $site, $siteHostConfig, $sitePathConfig] = $this->siteResolver->resolveSiteAndHost($request);

            if (!$siteId) {
                return null;
            }

            $request->attributes->set('_site', $siteId);
            $request->attributes->set('_sfs_cms_site', $site);
            $request->attributes->set('_sfs_cms_site_host_config', $siteHostConfig);
            $request->attributes->set('_sfs_cms_site_path_config', $sitePathConfig);
            $request->attributes->add($this->urlMatcher->matchRequest($request));

            return $request;
        } catch (SiteHasNotACanonicalHostException|Exception) {
            return null;
        }
    }

    public function generatePath($route, ?string $locale = null, $site = null): string
    {
        return $this->generateUrl($route, $locale, $site, UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    public function generateUrlForContent($content, string $locale, $site = null): string
    {
        foreach ($content->getRoutes() as $route) {
            if ($route->getPathForLocale($locale)) {
                return $this->generateUrl($route, $locale, $site);
            }
        }

        return '#';
    }

    public function generateUrl($route, ?string $locale = null, $site = null, int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL): string
    {
        if (is_null($route)) {
            return '#';
        }

        $params = [];
        $params['_locale'] = $locale ?: ($this->requestStack->getCurrentRequest()?->getLocale() ?: 'en');

        if ($site) {
            $params['_site'] = $site;
        }

        $routeName = $route;

        if (is_array($route)) {
            if (is_null($route['route_name'])) {
                return '#';
            }

            $params = array_merge($params, $route['route_params'] ?? []);
            $routeName = $route['route_name'];
        } elseif ($route instanceof RouteInterface) {
            $routeName = $route->getId();
        }

        return $this->router->generate($routeName, $params, $referenceType);
    }
}
