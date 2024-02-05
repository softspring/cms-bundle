<?php

namespace Softspring\CmsBundle\Test\Config\Sitemap;

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
    public function testGetResponse(): void
    {
        $sitemap = new Sitemap([], 3600);

        $response = $sitemap->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/xml', $response->headers->get('Content-type'));
    }

    public function testGetCacheTtlNull(): void
    {
        $sitemap = new Sitemap([]);

        $this->assertNull($sitemap->getCacheTtl());
    }

    public function testGetCacheTtl(): void
    {
        $sitemap = new Sitemap([], 3600);

        $this->assertEquals(3600, $sitemap->getCacheTtl());
    }

    public function testXml(): void
    {
        $sitemap = new Sitemap([
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
}
