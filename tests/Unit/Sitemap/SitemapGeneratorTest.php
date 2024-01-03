<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Sitemap;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Entity\Site;
use Softspring\CmsBundle\Routing\UrlGenerator;
use Softspring\CmsBundle\Sitemap\Index;
use Softspring\CmsBundle\Sitemap\Sitemap;
use Softspring\CmsBundle\Sitemap\SitemapGenerator;

class SitemapGeneratorTest extends TestCase
{
    public function testSitemap(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $urlGenerator = $this->createMock(UrlGenerator::class);

        $sitemapGenerator = new SitemapGenerator($em, $urlGenerator);

        $site = new Site();
        $site->setConfig([
            'sitemaps' => [
                'test' => []
            ]
        ]);
        $sitemap = $sitemapGenerator->sitemap($site, 'test');

        $this->assertInstanceOf(Sitemap::class, $sitemap);
    }

    public function testIndex(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $urlGenerator = $this->createMock(UrlGenerator::class);

        $sitemapGenerator = new SitemapGenerator($em, $urlGenerator);

        $site = new Site();
        $site->setConfig([]);
        $index = $sitemapGenerator->index($site);

        $this->assertInstanceOf(Index::class, $index);
    }
}
