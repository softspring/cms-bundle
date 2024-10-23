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
    public function generateRoutePathAlternates(RoutePathInterface $path, SiteInterface $site, bool $localeAlternates = true, bool $siteAlternates = true, bool $addHrefLang = true): array
    {
        $alternates = $localeAlternates ? $this->generateRoutePathAlternatesForSite($site, $path, $addHrefLang) : [];

        if (!$siteAlternates) {
            return $alternates;
        }

        foreach ($path->getRoute()->getSites()->filter(fn ($as) => $as !== $site) as $alternateSite) {
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
}
