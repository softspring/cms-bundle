<?php

namespace Softspring\CmsBundle\Sitemap;

use Softspring\CmsBundle\Model\SiteInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class Index implements XmlInterface
{
    protected array $siteConfig;

    public function __construct(
        protected SiteInterface $site,
    ) {
        $this->siteConfig = $this->site->getConfig() ?? [];
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
        return $this->siteConfig['sitemaps_index']['cache_ttl'] ?? null;
    }

    public function xml(): string
    {
        $xmlEncoder = new XmlEncoder();

        return $xmlEncoder->encode([
            '@xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
            'sitemap' => $this->sitemaps(),
        ], 'xml', [
            'xml_encoding' => 'UTF-8',
            'xml_format_output' => true,
            'remove_empty_tags' => true,
            'xml_root_node_name' => 'sitemapindex',
        ]);
    }

    /**
     * Generates the sitemaps urls for this site.
     */
    public function sitemaps(): array
    {
        $hostAndProtocol = ($this->site->getCanonicalScheme() ?? 'https').'://'.$this->site->getCanonicalHost();

        $sitemaps = [];

        foreach ($this->siteConfig['sitemaps'] as $sitemapName => $sitemapConfig) {
            $sitemaps[] = [
                'loc' => "$hostAndProtocol/{$sitemapConfig['url']}",
            ];
        }

        return $sitemaps;
    }
}
