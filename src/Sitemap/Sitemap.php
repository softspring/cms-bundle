<?php

namespace Softspring\CmsBundle\Sitemap;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class Sitemap implements XmlInterface
{
    public function __construct(protected array $urls, protected ?int $responseCacheTtl = null)
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
            '@xmlns:xhtml' => 'http://www.w3.org/1999/xhtml',
            'url' => $this->urls,
        ], 'xml', [
            'xml_encoding' => 'UTF-8',
            'xml_format_output' => true,
            'remove_empty_tags' => true,
            'xml_root_node_name' => 'urlset',
        ]);
    }
}
