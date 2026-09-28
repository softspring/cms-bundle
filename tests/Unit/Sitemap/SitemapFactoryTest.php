<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Sitemap;

use DateTime;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Helper\RoutingHelper;
use Softspring\CmsBundle\Model\ContentInterface;
use Softspring\CmsBundle\Model\Site;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Softspring\CmsBundle\Sitemap\InvalidSitemapException;
use Softspring\CmsBundle\Sitemap\SitemapFactory;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class SitemapFactoryTest extends TestCase
{
    protected AbstractQuery&MockObject $query;
    protected QueryBuilder&MockObject $qb;
    protected EntityManagerInterface&MockObject $em;
    protected UrlGenerator&MockObject $urlGenerator;
    protected RoutingHelper&MockObject $routingHelper;

    protected function setUp(): void
    {
        if (new ReflectionClass(Query::class)->isFinal()) {
            $this->query = $this->createMock(AbstractQuery::class);
        } else {
            $this->query = $this->createMock(Query::class);
        }

        $this->qb = $this->createMock(QueryBuilder::class);
        $this->qb->method('leftJoin')->willReturn($this->qb);
        $this->qb->method('andWhere')->willReturn($this->qb);
        $this->qb->method('setParameter')->willReturn($this->qb);
        $this->qb->method('getQuery')->willReturn($this->query);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')->with('c')->willReturn($this->qb);

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->method('getRepository')->with(ContentInterface::class)->willReturn($repository);

        $this->urlGenerator = $this->createMock(UrlGenerator::class);
        $this->routingHelper = $this->createMock(RoutingHelper::class);
    }

    public function testCreateBuildsSitemapFromPublishedIndexableContent(): void
    {
        $site = $this->createSite();
        $includedContent = $this->createContent([
            'sitemap' => true,
            'sitemapPriority' => '0.8',
            'sitemapChangefreq' => 'weekly',
        ]);
        $includedContent->addRoute($this->createRoutePath('es', 'inicio'));
        $includedContent->addRoute($this->createRoutePath('en', 'home'));
        $includedContent->addRoute($this->createRoutePath('fr', 'accueil'));

        $notInSitemapContent = $this->createContent(['sitemap' => false]);
        $notInSitemapContent->addRoute($this->createRoutePath('es', 'hidden'));

        $noIndexContent = $this->createContent(['sitemap' => true, 'noIndex' => true]);
        $noIndexContent->addRoute($this->createRoutePath('es', 'noindex'));

        $this->query->method('getResult')->willReturn([$includedContent, $notInSitemapContent, $noIndexContent]);
        $this->urlGenerator->method('getUrlFixed')->willReturnCallback(
            fn (RoutePath $path): string => sprintf('https://example.org/%s/%s', $path->getLocale(), $path->getPath())
        );
        $this->routingHelper->method('generateRoutePathAlternates')->willReturnCallback(
            fn (RoutePath $path, Site $site, bool $localeAlternates, bool $siteAlternates, bool $includeHreflang): array => [[
                '@rel' => 'alternate',
                '@hreflang' => $includeHreflang ? $path->getLocale() : null,
                '@href' => sprintf('https://example.org/%s/%s', $path->getLocale(), $path->getPath()),
                'localeAlternates' => $localeAlternates,
                'siteAlternates' => $siteAlternates,
            ]]
        );

        $sitemap = (new SitemapFactory($this->em, $this->urlGenerator, $this->routingHelper))->create($site, 'pages');

        $this->assertSame(3600, $sitemap->getCacheTtl());

        $data = (new XmlEncoder())->decode($sitemap->xml(), 'xml');

        $this->assertCount(2, $data['url']);
        $this->assertEquals([
            'loc' => 'https://example.org/es/inicio',
            'lastmod' => '2024-01-02',
            'changefreq' => 'weekly',
            'priority' => '0.8',
            'xhtml:link' => [
                '@rel' => 'alternate',
                '@hreflang' => 'es',
                '@href' => 'https://example.org/es/inicio',
                'localeAlternates' => '1',
                'siteAlternates' => '0',
            ],
        ], $data['url'][0]);
        $this->assertEquals('https://example.org/en/home', $data['url'][1]['loc']);
    }

    public function testGenerateSitemapContentUrlsUsesSitemapDefaults(): void
    {
        $site = $this->createSite();
        $content = $this->createContent(['sitemap' => true]);
        $content->addRoute($this->createRoutePath('es', 'inicio'));

        $this->urlGenerator->method('getUrlFixed')->willReturn('https://example.org/es/inicio');
        $this->routingHelper->method('generateRoutePathAlternates')->willReturn([]);

        $urls = (new SitemapFactory($this->em, $this->urlGenerator, $this->routingHelper))->generateSitemapContentUrls($site, $content, [
            'default_priority' => '0.5',
            'default_changefreq' => 'daily',
            'alternates_locales' => false,
            'alternates_sites' => false,
            'alternates_include_hreflang' => false,
        ]);

        $this->assertEquals([
            [
                'loc' => 'https://example.org/es/inicio',
                'lastmod' => '2024-01-02',
                'changefreq' => 'daily',
                'priority' => '0.5',
            ],
        ], $urls);
    }

    public function testCreateThrowsExceptionForUnknownSitemap(): void
    {
        $this->expectException(InvalidSitemapException::class);

        (new SitemapFactory($this->em, $this->urlGenerator, $this->routingHelper))->create($this->createSite(), 'missing');
    }

    protected function createSite(): Site
    {
        $site = new Site();
        $site->setId('default');
        $site->setConfig([
            'locales' => ['es', 'en'],
            'sitemaps' => [
                'pages' => [
                    'cache_ttl' => 3600,
                    'default_priority' => '0.5',
                    'default_changefreq' => 'daily',
                    'alternates_locales' => true,
                    'alternates_sites' => false,
                    'alternates_include_hreflang' => true,
                ],
            ],
        ]);

        return $site;
    }

    protected function createContent(array $indexing): Page
    {
        $content = new Page();
        $content->setIndexing($indexing);

        $version = new ContentVersion();
        $version->setCreatedAt(new DateTime('2024-01-02'));
        $content->setPublishedVersion($version);

        return $content;
    }

    protected function createRoutePath(string $locale, string $path): Route
    {
        $route = new Route();
        $routePath = new RoutePath();
        $routePath->setLocale($locale);
        $routePath->setPath($path);
        $route->addPath($routePath);

        return $route;
    }
}
