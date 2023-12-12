<?php

namespace Softspring\CmsBundle\Sitemap;

use Symfony\Component\HttpFoundation\Response;

interface XmlInterface
{
    /**
     * Generates a Response object with the sitemap content, including headers and cache control directives.
     */
    public function getResponse(): Response;

    /**
     * Returns the cache TTL in seconds configured for this sitemap.
     */
    public function getCacheTtl(): ?int;

    /**
     * Returns the sitemap content as XML string.
     */
    public function xml(): string;
}
