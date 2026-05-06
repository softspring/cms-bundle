<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Config\Exception;

use Exception;

class DisabledModuleException extends Exception
{
    public function __construct(string $module)
    {
        parent::__construct(sprintf('Module "%s" is disabled', $module));
    }
}
