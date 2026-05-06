<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Config\Exception;

use Exception;

class InvalidSiteException extends Exception
{
    public function __construct(string $site, array $sitesConfig)
    {
        parent::__construct(sprintf('Invalid site "%s", posible values: %s', $site, implode(', ', array_keys($sitesConfig))));
    }
}
