<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Sitemap;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Model\Site;
use Softspring\CmsBundle\Sitemap\Index;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class IndexTest extends TestCase
{
    public function testGetResponse(): void
    {
        $index = new Index([], 3600);

        $response = $index->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/xml', $response->headers->get('Content-type'));
    }

    public function testGetCacheTtlNull(): void
    {
        $index = new Index([]);

        $this->assertNull($index->getCacheTtl());
    }

    public function testGetCacheTtl(): void
    {
        $index = new Index([], 3600);

        $this->assertEquals(3600, $index->getCacheTtl());
    }


    public function testXml(): void
    {
        $index = new Index([
            'loc' => 'https://example.com/sitemap.xml',
        ]);

        $xml = $index->xml();

        $xmlEncoder = new XmlEncoder();
        $data = $xmlEncoder->decode($xml, 'xml');

        $this->assertArrayHasKey('@xmlns', $data);
        $this->assertArrayHasKey('sitemap', $data);

        $this->assertArrayHasKey('loc', $data['sitemap']);
        $this->assertEquals('https://example.com/sitemap.xml', $data['sitemap']['loc']);
    }

    public function testSitemaps(): void
    {
        $index = new Index([
            ['loc' => 'https://example.com/sitemap1.xml'],
            ['loc' => 'https://example.com/sitemap2.xml'],
        ]);
        $sitemapsXml = $index->xml();

        $this->assertStringContainsString('https://example.com/sitemap1.xml', $sitemapsXml);
        $this->assertStringContainsString('https://example.com/sitemap2.xml', $sitemapsXml);
    }
}
