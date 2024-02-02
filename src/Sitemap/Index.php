<?php

namespace Softspring\CmsBundle\Sitemap;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class Index implements XmlInterface
{
    public function __construct(protected array $sitemapsUrls, protected ?int $responseCacheTtl = null)
    {
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
        return $this->responseCacheTtl;
    }

    public function xml(): string
    {
        $xmlEncoder = new XmlEncoder();

        return $xmlEncoder->encode([
            '@xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
            'sitemap' => $this->sitemapsUrls,
        ], 'xml', [
            'xml_encoding' => 'UTF-8',
            'xml_format_output' => true,
            'remove_empty_tags' => true,
            'xml_root_node_name' => 'sitemapindex',
        ]);
    }
}
