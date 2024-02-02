<?php

namespace Softspring\CmsBundle\Sitemap;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Helper\RoutingHelper;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Routing\UrlGenerator;

class SitemapFactory
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected UrlGenerator $urlGenerator,
        protected RoutingHelper $routingHelper,
    ) {
    }

    /**
     * @throws InvalidSitemapException
     */
    public function create(SiteInterface $site, string $sitemapId): Sitemap
    {
        $sitemapConfig = $this->getSitemapConfig($site, $sitemapId);

        $urls = [];
        foreach ($this->getSiteContents($site)->filter(fn ($c) => !$this->skipContent($c)) as $content) {
            $urls = array_merge($urls, $this->generateSitemapContentUrls($site, $content, $sitemapConfig));
        }

        return new Sitemap($urls, $sitemapConfig['cache_ttl'] ?? null);
    }

    /**
     * Generates the sitemap urls for all routes of a content.
     */
    public function generateSitemapContentUrls(SiteInterface $site, ContentInterface $content, array $sitemapConfig): array
    {
        $urls = [];

        /** @deprecated will only use alternates_locales */
        $localeAlternates = $sitemapConfig['alternates'] || $sitemapConfig['alternates_locales'];
        /* @deprecated will only use alternates_sites */
        $siteAlternates = $sitemapConfig['alternates'] || $sitemapConfig['alternates_sites'];

        foreach ($content->getRoutes() as $route) {
            foreach ($route->getPaths() as $path) {
                if (!in_array($path->getLocale(), $site->getConfig()['locales'])) {
                    continue;
                }

                $urls[] = array_filter([
                    'loc' => $this->urlGenerator->getUrlFixed($path, $site),
                    'lastmod' => $content->getPublishedVersion()?->getCreatedAt()->format('Y-m-d'),
                    'changefreq' => $this->getChangeFreq($content, $sitemapConfig),
                    'priority' => $this->getPriority($content, $sitemapConfig),
                    'xhtml:link' => $this->routingHelper->generateRoutePathAlternates($path, $site, $localeAlternates, $siteAlternates),
                ], fn ($v) => !empty($v));
            }
        }

        return $urls;
    }

    protected function getChangeFreq(ContentInterface $content, array $sitemapConfig): ?string
    {
        $seo = $content->getSeo();

        if (!empty($seo['sitemapChangefreq'])) {
            return $seo['sitemapChangefreq'];
        }

        if (!empty($sitemapConfig['default_changefreq'])) {
            return $sitemapConfig['default_changefreq'];
        }

        return null;
    }

    protected function getPriority(ContentInterface $content, array $sitemapConfig): ?string
    {
        $seo = $content->getSeo();

        if (!empty($seo['sitemapPriority'])) {
            return sprintf('%.1F', floatval($seo['sitemapPriority']));
        }

        if (!empty($sitemapConfig['default_priority'])) {
            return sprintf('%.1F', floatval($sitemapConfig['default_priority']));
        }

        return null;
    }

    /**
     * Returns the content entities published in this site.
     */
    protected function getSiteContents(SiteInterface $site): ArrayCollection
    {
        $qb = $this->em->getRepository(ContentInterface::class)->createQueryBuilder('c');
        $qb->leftJoin('c.sites', 's');
        $qb->andWhere('c.publishedVersion IS NOT NULL');
        $qb->andWhere('s = :site')->setParameter('site', $site);

        return new ArrayCollection($qb->getQuery()->getResult());
    }

    /**
     * @throws InvalidSitemapException
     */
    protected function getSitemapConfig(SiteInterface $site, string $sitemapId): array
    {
        $siteConfig = $site->getConfig() ?? [];

        if (!isset($siteConfig['sitemaps'][$sitemapId])) {
            throw new InvalidSitemapException($sitemapId);
        }

        return $siteConfig['sitemaps'][$sitemapId];
    }

    /**
     * Returns true if the content should be skipped in the sitemap looking at its SEO configuration.
     */
    protected function skipContent(ContentInterface $content): bool
    {
        $seo = $content->getSeo();
        if (!($seo['sitemap'] ?? false)) {
            return true;
        }

        // TODO check sitemap name

        if ($seo['noIndex'] ?? false) {
            return true;
        }

        return false;
    }
}
