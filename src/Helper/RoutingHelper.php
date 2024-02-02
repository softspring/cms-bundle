<?php

namespace Softspring\CmsBundle\Helper;

use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Routing\UrlGenerator;

class RoutingHelper
{
    public function __construct(
        protected UrlGenerator $urlGenerator,
    ) {
    }

    /**
     * Generates the alternate URLs for a route path in all sites and languages.
     */
    public function generateRoutePathAlternates(RoutePathInterface $path, SiteInterface $site, bool $localeAlternates = true, bool $siteAlternates = true): array
    {
        $alternates = $localeAlternates ? $this->generateRoutePathAlternatesForSite($site, $path) : [];

        if (!$siteAlternates) {
            return $alternates;
        }

        foreach ($path->getRoute()->getSites()->filter(fn ($as) => $as !== $site) as $alternateSite) {
            $alternates = array_merge($alternates, $this->generateRoutePathAlternatesForSite($alternateSite, $path));
        }

        return $alternates;
    }

    /**
     * Generates the alternate URLs for a route path in a site.
     */
    public function generateRoutePathAlternatesForSite(SiteInterface $site, RoutePathInterface $path): array
    {
        $alternates = [];

        foreach ($path->getRoute()->getPaths() as $alternatePath) {
            if (!in_array($alternatePath->getLocale(), $site->getConfig()['locales'])) {
                continue;
            }

            $alternates[] = [
                '@rel' => 'alternate',
                '@hreflang' => $alternatePath->getLocale(),
                '@href' => $this->urlGenerator->getUrlFixed($alternatePath, $site),
            ];
        }

        return $alternates;
    }
}
