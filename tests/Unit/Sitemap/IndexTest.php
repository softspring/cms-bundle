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
        $site = new Site();
        $site->setConfig([
            'sitemaps_index' => [
                'cache_ttl' => 3600,
            ]
        ]);

        $index = $this->getMockBuilder(Index::class,)
            ->setConstructorArgs([$site])
            ->onlyMethods(['xml'])
            ->getMock();

        $response = $index->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/xml', $response->headers->get('Content-type'));
    }

    public function testGetCacheTtlNull(): void
    {
        $site = new Site();
        $site->setConfig([
            'sitemaps_index' => [
            ]
        ]);
        $index = new Index($site);

        $this->assertNull($index->getCacheTtl());
    }

    public function testGetCacheTtl(): void
    {
        $site = new Site();
        $site->setConfig([
            'sitemaps_index' => [
                'cache_ttl' => 3600,
            ]
        ]);
        $index = new Index($site);

        $this->assertEquals(3600, $index->getCacheTtl());
    }


    public function testXml(): void
    {
        $site = new Site();
        $site->setConfig([
            'sitemaps' => [
                'test' => []
            ]
        ]);

        $index = $this->getMockBuilder(Index::class,)
            ->setConstructorArgs([$site])
            ->onlyMethods(['sitemaps'])
            ->getMock();
        $index->method('sitemaps')->willReturn([
            [
                'loc' => 'https://example.com/sitemap.xml',
            ]
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
        $site = new Site();
        $site->setConfig([
            'hosts' => [
                [
                    'domain' => 'example.com',
                    'scheme' => 'https',
                    'canonical' => true,
                ]
            ],
            'sitemaps' => [
                'sitemap1' => [
                    'url' => 'sitemap1.xml',
                ],
                'sitemap2' => [
                    'url' => 'sitemap2.xml',
                ],
            ]
        ]);

        $index = new Index($site);
        $sitemaps = $index->sitemaps();

        $this->assertEquals('https://example.com/sitemap1.xml', $sitemaps[0]['loc']);
        $this->assertEquals('https://example.com/sitemap2.xml', $sitemaps[1]['loc']);
    }
}
