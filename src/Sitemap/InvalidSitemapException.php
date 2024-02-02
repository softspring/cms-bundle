<?php

namespace Softspring\CmsBundle\Sitemap;

class InvalidSitemapException extends \Exception
{
    public function __construct(string $sitemap, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Invalid "%s" sitemap for this site', $sitemap), $code, $previous);
    }
}
