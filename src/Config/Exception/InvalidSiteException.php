<?php

namespace Softspring\CmsBundle\Config\Exception;

class InvalidSiteException extends \Exception
{
    public function __construct(string $site, array $sitesConfig)
    {
        parent::__construct(sprintf('Invalid site "%s", posible values: %s', $site, implode(', ', array_keys($sitesConfig))));
    }
}
