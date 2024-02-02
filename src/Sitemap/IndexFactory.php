<?php

namespace Softspring\CmsBundle\Sitemap;

use Softspring\CmsBundle\Model\SiteInterface;

class IndexFactory
{
    public function create(SiteInterface $site): Index
    {
        $siteConfig = $site->getConfig() ?? [];
        $sitemapsUrls = $this->generateSitemapsUrls($site, $siteConfig);

        return new Index($sitemapsUrls, $siteConfig['sitemaps_index']['cache_ttl'] ?? null);
    }

    protected function generateSitemapsUrls(SiteInterface $site, array $siteConfig): array
    {
        $hostAndProtocol = ($site->getCanonicalScheme() ?? 'https').'://'.$site->getCanonicalHost();

        $sitemaps = [];

        foreach ($siteConfig['sitemaps'] as $sitemapName => $sitemapConfig) {
            $sitemaps[] = [
                'loc' => "$hostAndProtocol/{$sitemapConfig['url']}",
            ];
        }

        return $sitemaps;
    }
}
