<?php

namespace Softspring\CmsBundle\Sitemap;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\RoutePathInterface;
use Softspring\CmsBundle\Model\SiteInterface;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class Sitemap implements XmlInterface
{
    protected array $siteConfig;
    protected array $sitemapConfig;

    /**
     * @throws InvalidSitemapException
     */
    public function __construct(
        protected SiteInterface $site,
        protected string $sitemap,
        protected EntityManagerInterface $em,
        protected UrlGenerator $urlGenerator,
    ) {
        $this->siteConfig = $this->site->getConfig() ?? [];

        if (!isset($this->siteConfig['sitemaps'][$sitemap])) {
            throw new InvalidSitemapException($sitemap);
        }
        $this->sitemapConfig = $this->siteConfig['sitemaps'][$sitemap];
    }

    public function getResponse(): Response
    {
        $response = new Response($this->xml(), 200, ['Content-type' => 'application/xml']);

        if ($this->getCacheTtl()) {
            $response->setPublic();
            $response->setMaxAge($this->getCacheTtl());
        }

        return $response;
    }

    public function getCacheTtl(): ?int
    {
        return $this->sitemapConfig['cache_ttl'] ?? null;
    }

    public function xml(): string
    {
        $xmlEncoder = new XmlEncoder();

        return $xmlEncoder->encode([
            '@xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
            '@xmlns:xhtml' => 'http://www.w3.org/1999/xhtml',
            'url' => $this->urls(),
        ], 'xml', [
            'xml_encoding' => 'UTF-8',
            'xml_format_output' => true,
            'remove_empty_tags' => true,
            'xml_root_node_name' => 'urlset',
        ]);
    }

    /**
     * Generates the sitemap urls for published contents.
     */
    public function urls(): array
    {
        $urls = [];

        foreach ($this->getSiteContents()->filter(fn ($c) => !$this->skipContent($c)) as $content) {
            $urls = array_merge($urls, $this->generateContentUrls($content));
        }

        return $urls;
    }

    /**
     * Generates the sitemap urls for all routes of a content.
     */
    public function generateContentUrls(ContentInterface $content): array
    {
        $urls = [];

        foreach ($content->getRoutes() as $route) {
            foreach ($route->getPaths() as $path) {
                if (!in_array($path->getLocale(), $this->site->getConfig()['locales'])) {
                    continue;
                }

                $urls[] = array_filter([
                    'loc' => $this->urlGenerator->getUrlFixed($path, $this->site),
                    'lastmod' => $content->getPublishedVersion()?->getCreatedAt()->format('Y-m-d'),
                    'changefreq' => $this->getChangeFreq($content),
                    'priority' => $this->getPriority($content),
                    'xhtml:link' => $this->generateRoutePathAlternates($path),
                ], fn ($v) => !empty($v));
            }
        }

        return $urls;
    }

    protected function getChangeFreq(ContentInterface $content): ?string
    {
        $seo = $content->getSeo();

        if (!empty($seo['sitemapChangefreq'])) {
            return $seo['sitemapChangefreq'];
        }

        if (!empty($this->sitemapConfig['default_changefreq'])) {
            return $this->sitemapConfig['default_changefreq'];
        }

        return null;
    }

    protected function getPriority(ContentInterface $content): ?string
    {
        $seo = $content->getSeo();

        if (!empty($seo['sitemapPriority'])) {
            return sprintf('%.1F', floatval($seo['sitemapPriority']));
        }

        if (!empty($this->sitemapConfig['default_priority'])) {
            return sprintf('%.1F', floatval($this->sitemapConfig['default_priority']));
        }

        return null;
    }

    /**
     * Generates the alternate URLs for a route path in all sites and languages.
     */
    public function generateRoutePathAlternates(RoutePathInterface $path): array
    {
        /** @deprecated will only use alternates_locales */
        $alternateLocales = $this->sitemapConfig['alternates'] || $this->sitemapConfig['alternates_locales'];
        $alternates = $alternateLocales ? $this->generateSiteRoutePathAlternates($this->site, $path) : [];

        /* @deprecated will only use alternates_sites */
        if (!$this->sitemapConfig['alternates'] || !$this->sitemapConfig['alternates_sites']) {
            return $alternates;
        }

        foreach ($path->getRoute()->getSites()->filter(fn ($as) => $as !== $this->site) as $alternateSite) {
            $alternates = array_merge($alternates, $this->generateSiteRoutePathAlternates($alternateSite, $path));
        }

        return $alternates;
    }

    /**
     * Generates the alternate URLs for a route path in a site.
     */
    public function generateSiteRoutePathAlternates(SiteInterface $site, RoutePathInterface $path): array
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

    /**
     * Returns true if the content should be skipped in the sitemap looking at its SEO configuration.
     */
    public function skipContent(ContentInterface $content): bool
    {
        $seo = $content->getSeo();
        if (!($seo['sitemap'] ?? false)) {
            return true;
        }

        // TODO check sitemap name with $this->sitemap

        if ($seo['noIndex'] ?? false) {
            return true;
        }

        return false;
    }

    /**
     * Returns the content entities published in this site.
     */
    public function getSiteContents(): Collection
    {
        $qb = $this->em->getRepository(ContentInterface::class)->createQueryBuilder('c');
        $qb->leftJoin('c.sites', 's');
        $qb->andWhere('c.publishedVersion IS NOT NULL');
        $qb->andWhere('s = :site')->setParameter('site', $this->site);

        return new ArrayCollection($qb->getQuery()->getResult());
    }
}
