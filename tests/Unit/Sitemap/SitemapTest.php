<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Sitemap;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\ContentVersion;
use Softspring\CmsBundle\Entity\Page;
use Softspring\CmsBundle\Entity\Route;
use Softspring\CmsBundle\Entity\RoutePath;
use Softspring\CmsBundle\Model\Site;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Softspring\CmsBundle\Sitemap\InvalidSitemapException;
use Softspring\CmsBundle\Sitemap\Sitemap;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class SitemapTest extends TestCase
{
    protected EntityManagerInterface|MockObject $em;
    protected UrlGenerator|MockObject $urlGenerator;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->urlGenerator = $this->createMock(UrlGenerator::class);
    }

    public function testInvalidSitemap(): void
    {
        $this->expectException(InvalidSitemapException::class);

        $site = new Site();
        new Sitemap($site, 'invalid', $this->em, $this->urlGenerator);
    }

    public function testGetResponse(): void
    {
        $site = new Site();
        $site->setConfig([
            'sitemaps' => [
                'test' => [
                    'cache_ttl' => 3600,
                ]
            ]
        ]);

        $sitemap = $this->getMockBuilder(Sitemap::class,)
            ->setConstructorArgs([$site, 'test', $this->em, $this->urlGenerator])
            ->onlyMethods(['xml'])
            ->getMock();

        $response = $sitemap->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/xml', $response->headers->get('Content-type'));
    }

    public function testGetCacheTtlNull(): void
    {
        $site = new Site();
        $site->setConfig([
            'sitemaps' => [
                'test' => []
            ]
        ]);
        $sitemap = new Sitemap($site, 'test', $this->em, $this->urlGenerator);

        $this->assertNull($sitemap->getCacheTtl());
    }

    public function testGetCacheTtl(): void
    {
        $site = new Site();
        $site->setConfig([
            'sitemaps' => [
                'test' => [
                    'cache_ttl' => 3600,
                ]
            ]
        ]);
        $sitemap = new Sitemap($site, 'test', $this->em, $this->urlGenerator);

        $this->assertEquals(3600, $sitemap->getCacheTtl());
    }

    public function testXml(): void
    {
        $site = new Site();
        $site->setConfig([
            'sitemaps' => [
                'test' => []
            ]
        ]);

        $sitemap = $this->getMockBuilder(Sitemap::class,)
            ->setConstructorArgs([$site, 'test', $this->em, $this->urlGenerator])
            ->onlyMethods(['urls'])
            ->getMock();
        $sitemap->method('urls')->willReturn([
            [
                'loc' => 'https://example.com',
                'lastmod' => '2021-01-01',
                'changefreq' => 'daily',
                'priority' => '0.5',
                'xhtml:link' => [],
            ],
            [
                'loc' => 'https://example.com',
                'lastmod' => null,
                'changefreq' => null,
                'priority' => null,
                'xhtml:link' => [],
            ],
        ]);

        $xml = $sitemap->xml();

        $xmlEncoder = new XmlEncoder();
        $data = $xmlEncoder->decode($xml, 'xml');

        $this->assertArrayHasKey('@xmlns', $data);
        $this->assertArrayHasKey('@xmlns:xhtml', $data);
        $this->assertArrayHasKey('url', $data);

        $this->assertEquals([
            'loc' => 'https://example.com',
            'lastmod' => '2021-01-01',
            'changefreq' => 'daily',
            'priority' => '0.5',
            'xhtml:link' => '',
        ], $data['url'][0]);

        $this->assertEquals([
            'loc' => 'https://example.com',
            'xhtml:link' => '',
        ], $data['url'][1]);
    }

    public function testUrlsWithAlternates(): void
    {
        $site1 = new Site();
        $site1->setId('site1');
        $site1->setConfig([
            'sitemaps' => [
                'test' => [
                    'alternates' => true,
                    'alternates_locales' => true,
                    'alternates_sites' => true,
                ]
            ],
            'locales' => ['es', 'en'],
        ]);

        $site2 = new Site();
        $site2->setId('site2');
        $site2->setConfig([
            'locales' => ['de'],
        ]);

        $content1 = new Page();
        $content1->setSeo([
            'sitemap' => true,
            'noIndex' => false,
        ]);
        $content1->addSite($site1);
        $content1->addSite($site2);
        $content1->addRoute($route1 = new Route());
        $route1->addSite($site1);
        $route1->addSite($site2);
        $route1->addPath($path1_1 = new RoutePath());
        $path1_1->setPath('/path-1-1');
        $path1_1->setLocale('es');
        $route1->addPath($path1_2 = new RoutePath());
        $path1_2->setPath('/path-1-2');
        $path1_2->setLocale('en');
        $route1->addPath($path1_3 = new RoutePath());
        $path1_3->setPath('/path-1-3');
        $path1_3->setLocale('de');

        $content2 = new Page();

        $contentsCollection = new ArrayCollection([
            $content1,
            $content2,
        ]);

        $sitemap = $this->getMockBuilder(Sitemap::class,)
            ->setConstructorArgs([$site1, 'test', $this->em, $this->urlGenerator])
            ->onlyMethods(['getSiteContents'])
            ->getMock();
        $sitemap->method('getSiteContents')->willReturn($contentsCollection);

        $this->urlGenerator->method('getUrlFixed')->willReturnCallback(function (RoutePath $path, Site $site) {
            return $site->getId().'/'.$path->getLocale().'/'.$path->getCompiledPath();
        });

        $urls = $sitemap->urls();

        $this->assertCount(2, $urls);

        $this->assertEquals('site1/es/path-1-1', $urls[0]['loc']);
        $this->assertEquals('site1/es/path-1-1', $urls[0]['xhtml:link'][0]['@href']);
        $this->assertEquals('site1/en/path-1-2', $urls[0]['xhtml:link'][1]['@href']);
        $this->assertEquals('site2/de/path-1-3', $urls[0]['xhtml:link'][2]['@href']);

        $this->assertEquals('site1/en/path-1-2', $urls[1]['loc']);
        $this->assertEquals('site1/es/path-1-1', $urls[1]['xhtml:link'][0]['@href']);
        $this->assertEquals('site1/en/path-1-2', $urls[1]['xhtml:link'][1]['@href']);
        $this->assertEquals('site2/de/path-1-3', $urls[1]['xhtml:link'][2]['@href']);
    }

    public function testUrlsWithoutAlternates(): void
    {
        $site1 = new Site();
        $site1->setId('site1');
        $site1->setConfig([
            'sitemaps' => [
                'test' => [
                    'alternates' => false,
                    'alternates_locales' => false,
                    'alternates_sites' => false,
                ]
            ],
            'locales' => ['es', 'en'],
        ]);

        $site2 = new Site();
        $site2->setId('site2');
        $site2->setConfig([
            'locales' => ['de'],
        ]);

        $content1 = new Page();
        $content1->setSeo([
            'sitemap' => true,
            'noIndex' => false,
        ]);
        $content1->addSite($site1);
        $content1->addSite($site2);
        $content1->addRoute($route1 = new Route());
        $route1->addSite($site1);
        $route1->addSite($site2);
        $route1->addPath($path1_1 = new RoutePath());
        $path1_1->setPath('/path-1-1');
        $path1_1->setLocale('es');
        $route1->addPath($path1_2 = new RoutePath());
        $path1_2->setPath('/path-1-2');
        $path1_2->setLocale('en');
        $route1->addPath($path1_3 = new RoutePath());
        $path1_3->setPath('/path-1-3');
        $path1_3->setLocale('de');

        $content2 = new Page();

        $contentsCollection = new ArrayCollection([
            $content1,
            $content2,
        ]);

        $sitemap = $this->getMockBuilder(Sitemap::class,)
            ->setConstructorArgs([$site1, 'test', $this->em, $this->urlGenerator])
            ->onlyMethods(['getSiteContents'])
            ->getMock();
        $sitemap->method('getSiteContents')->willReturn($contentsCollection);

        $this->urlGenerator->method('getUrlFixed')->willReturnCallback(function (RoutePath $path, Site $site) {
            return $site->getId().'/'.$path->getLocale().'/'.$path->getCompiledPath();
        });

        $urls = $sitemap->urls();

        $this->assertCount(2, $urls);

        $this->assertEquals('site1/es/path-1-1', $urls[0]['loc']);
        $this->assertEquals('site1/en/path-1-2', $urls[1]['loc']);
    }

    public function testUrlAttributes(): void
    {
        $site = new Site();
        $site->setId('site1');
        $site->setConfig([
            'sitemaps' => [
                'test' => [
                    'alternates' => false,
                    'alternates_locales' => false,
                    'alternates_sites' => false,
                ]
            ],
            'locales' => ['es', 'en'],
        ]);

        $content1 = new Page();
        $content1->setPublishedVersion($version = new ContentVersion());
        $version->setCreatedAt(new \DateTime('2000-01-01'));
        $content1->setSeo([
            'sitemapChangefreq' => 'daily',
            'sitemapPriority' => 1.0,
            'sitemap' => true,
            'noIndex' => false,
        ]);
        $content1->addSite($site);
        $content1->addRoute($route1 = new Route());
        $route1->addPath($path1_1 = new RoutePath());
        $path1_1->setPath('/path-1-1');
        $path1_1->setLocale('es');
        $route1->addSite($site);

        $content2 = new Page();
        $content2->setSeo([
            'sitemap' => true,
            'noIndex' => false,
        ]);
        $content2->addSite($site);
        $content2->addRoute($route2 = new Route());
        $route2->addPath($path2_1 = new RoutePath());
        $path2_1->setPath('/path-1-1');
        $path2_1->setLocale('es');
        $route2->addSite($site);

        $contentsCollection = new ArrayCollection([
            $content1,
            $content2,
        ]);

        $sitemap = $this->getMockBuilder(Sitemap::class,)
            ->setConstructorArgs([$site, 'test', $this->em, $this->urlGenerator])
            ->onlyMethods(['getSiteContents'])
            ->getMock();
        $sitemap->method('getSiteContents')->willReturn($contentsCollection);

        $this->urlGenerator->method('getUrlFixed')->willReturnCallback(function (RoutePath $path, Site $site) {
            return $site->getId().'/'.$path->getLocale().'/'.$path->getCompiledPath();
        });

        $urls = $sitemap->urls();

        $this->assertEquals([
            'loc' => 'site1/es/path-1-1',
            'changefreq' => 'daily',
            'priority' => '1.0',
            'lastmod' => '2000-01-01',
        ], $urls[0]);

        $this->assertEquals([
            'loc' => 'site1/es/path-1-1',
        ], $urls[1]);
    }

    public function testUrlAttributesWithDefaultSiteValues(): void
    {
        $site = new Site();
        $site->setId('site1');
        $site->setConfig([
            'sitemaps' => [
                'test' => [
                    'default_changefreq' => 'monthly',
                    'default_priority' => 0.1,
                    'alternates' => false,
                    'alternates_locales' => false,
                    'alternates_sites' => false,
                ]
            ],
            'locales' => ['es', 'en'],
        ]);

        $content1 = new Page();
        $content1->setSeo([
            'sitemapChangefreq' => 'daily',
            'sitemapPriority' => 1.0,
            'sitemap' => true,
            'noIndex' => false,
        ]);
        $content1->addSite($site);
        $content1->addRoute($route1 = new Route());
        $route1->addPath($path1_1 = new RoutePath());
        $path1_1->setPath('/path-1-1');
        $path1_1->setLocale('es');
        $route1->addSite($site);

        $content2 = new Page();
        $content2->setSeo([
            'sitemap' => true,
            'noIndex' => false,
        ]);
        $content2->addSite($site);
        $content2->addRoute($route2 = new Route());
        $route2->addPath($path2_1 = new RoutePath());
        $path2_1->setPath('/path-1-1');
        $path2_1->setLocale('es');
        $route2->addSite($site);

        $contentsCollection = new ArrayCollection([
            $content1,
            $content2,
        ]);

        $sitemap = $this->getMockBuilder(Sitemap::class,)
            ->setConstructorArgs([$site, 'test', $this->em, $this->urlGenerator])
            ->onlyMethods(['getSiteContents'])
            ->getMock();
        $sitemap->method('getSiteContents')->willReturn($contentsCollection);

        $this->urlGenerator->method('getUrlFixed')->willReturnCallback(function (RoutePath $path, Site $site) {
            return $site->getId().'/'.$path->getLocale().'/'.$path->getCompiledPath();
        });

        $urls = $sitemap->urls();

        $this->assertEquals([
            'loc' => 'site1/es/path-1-1',
            'changefreq' => 'daily',
            'priority' => '1.0',
        ], $urls[0]);

        $this->assertEquals([
            'loc' => 'site1/es/path-1-1',
            'changefreq' => 'monthly',
            'priority' => '0.1',
        ], $urls[1]);
    }

    public function testSkipContents(): void
    {
        $site = new Site();
        $site->setConfig([
            'sitemaps' => [
                'test' => []
            ]
        ]);
        $sitemap = new Sitemap($site, 'test', $this->em, $this->urlGenerator);

        $content = new Page();
        $content->setSeo([
            'sitemap' => false,
        ]);
        $this->assertTrue($sitemap->skipContent($content));

        $content = new Page();
        $content->setSeo([
            'sitemap' => true,
            'noIndex' => true,
        ]);
        $this->assertTrue($sitemap->skipContent($content));

        $content = new Page();
        $content->setSeo([
            'sitemap' => true,
            'noIndex' => false,
        ]);
        $this->assertFalse($sitemap->skipContent($content));
    }
}
