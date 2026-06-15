<?php

namespace Softspring\CmsBundle\Routing;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Exception\SiteHasNotACanonicalHostException;
use Softspring\CmsBundle\Exception\SiteNotFoundException;
use Softspring\CmsBundle\Exception\SiteResolutionException;
use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\HttpFoundation\Request;

class SiteResolver
{
    protected CmsConfig $cmsConfig;
    protected array $siteConfig;

    public function __construct(CmsConfig $cmsConfig, array $siteConfig)
    {
        $this->cmsConfig = $cmsConfig;
        $this->siteConfig = $siteConfig;
    }

    /**
     * @throws SiteNotFoundException
     * @throws SiteResolutionException
     */
    public function resolveSiteAndHost(Request $request): ?array
    {
        if ($matchingSite = $this->matchingSite($request)) {
            return $matchingSite;
        }

        if ($this->siteConfig['throw_not_found']) {
            throw new SiteNotFoundException();
        }

        return [null, null, null, null];
    }

    protected function matchingSite(Request $request): ?array
    {
        $matchingSites = [];

        $host = $request->getHost();
        $path = $request->getPathInfo();

        foreach ($this->cmsConfig->getSites() as $siteId => $site) {
            $siteConfig = $site->getConfig();
            $matchScore = 0;
            $matchHostConfig = null;
            $matchPathConfig = null;
            $requireHost = (bool) count($siteConfig['hosts'] ?? []);
            $requirePath = (bool) count($siteConfig['paths'] ?? []);

            if (!empty($siteConfig['hosts'])) {
                foreach ($siteConfig['hosts'] as $hostConfig) {
                    if ($host === $hostConfig['domain']) {
                        $matchHostConfig = $hostConfig;
                        $matchScore += 100;
                    }
                }
            }

            if (!empty($siteConfig['paths'])) {
                foreach ($siteConfig['paths'] as $pathConfig) {
                    if ($this->pathMatches($path, $pathConfig['path']) && strlen($pathConfig['path']) > strlen($matchPathConfig['path'] ?? '')) {
                        $matchPathConfig = $pathConfig;
                    }
                }

                if ($matchPathConfig) {
                    $matchScore += strlen($matchPathConfig['path']);
                }
            }

            // check robots and sitemaps
            if (!$matchPathConfig) {
                foreach ($siteConfig['sitemaps'] ?? [] as $sitemapConfig) {
                    if ('/'.trim($sitemapConfig['url'], '/') === $path) {
                        $requirePath = false;
                        $matchPathConfig = null;
                        ++$matchScore;
                    }
                }
                if (($siteConfig['sitemaps_index']['enabled'] ?? false) && '/'.trim($siteConfig['sitemaps_index']['url'], '/') === $path) {
                    $requirePath = false;
                    $matchPathConfig = null;
                    ++$matchScore;
                }
                if (($siteConfig['robots']['mode'] ?? false) && '/robots.txt' === $path) {
                    $requirePath = false;
                    $matchPathConfig = null;
                    ++$matchScore;
                }
            }

            if ($matchScore > 0 && ($requireHost && $matchHostConfig || !$requireHost) && ($requirePath && $matchPathConfig || !$requirePath)) {
                $matchingSites[] = [
                    $matchScore,
                    [$siteId, $site, $matchHostConfig, $matchPathConfig],
                ];
            }
        }

        if ([] === $matchingSites) {
            return null;
        }

        usort($matchingSites, function (array $a, array $b): int {
            return $b[0] <=> $a[0];
        });

        return $matchingSites[0][1];
    }

    protected function pathMatches(string $requestPath, string $sitePath): bool
    {
        return $requestPath === $sitePath || str_starts_with($requestPath, "$sitePath/");
    }

    /**
     * @throws SiteHasNotACanonicalHostException
     */
    public function getCanonicalRedirectUrl(SiteInterface $site, Request $request): string
    {
        $canonicalHost = $site->getCanonicalHost() ?? '';

        if ('' === $canonicalHost || '0' === $canonicalHost) {
            throw new SiteHasNotACanonicalHostException();
        }

        $canonicalPort = $site->getCanonicalPort() ? ":{$site->getCanonicalPort()}" : '';
        $canonicalScheme = $site->getCanonicalScheme() ?? $request->getScheme();
        $queryString = $request->getQueryString() ? "?{$request->getQueryString()}" : '';

        return "$canonicalScheme://$canonicalHost$canonicalPort{$request->getPathInfo()}$queryString";
    }
}
