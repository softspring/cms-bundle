<?php

namespace Softspring\CmsBundle\Test\Unit\Config\Sitemap;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Model\Site;
use Softspring\CmsBundle\Sitemap\IndexFactory;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class IndexFactoryTest extends TestCase
{
    public function testCreateBuildsIndexWithConfiguredAndExternalSitemaps(): void
    {
        $site = new Site();
        $site->setId('default');
        $site->setConfig([
            'hosts' => [
                ['domain' => 'www.example.org', 'scheme' => 'https', 'canonical' => false],
                ['domain' => 'example.org', 'scheme' => 'https', 'canonical' => true],
            ],
            'sitemaps' => [
                'pages' => ['url' => 'sitemap-pages.xml'],
                'posts' => ['url' => 'sitemap-posts.xml'],
            ],
            'sitemaps_index' => [
                'cache_ttl' => 7200,
                'external_sitemaps' => [
                    'https://cdn.example.org/sitemap-assets.xml',
                ],
            ],
        ]);

        $index = (new IndexFactory())->create($site);

        $this->assertSame(7200, $index->getCacheTtl());

        $data = (new XmlEncoder())->decode($index->xml(), 'xml');

        $this->assertEquals([
            ['loc' => 'https://example.org/sitemap-pages.xml'],
            ['loc' => 'https://example.org/sitemap-posts.xml'],
            ['loc' => 'https://cdn.example.org/sitemap-assets.xml'],
        ], $data['sitemap']);
    }
}
