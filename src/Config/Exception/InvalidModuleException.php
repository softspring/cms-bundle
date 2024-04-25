<?php

namespace Softspring\CmsBundle\Config\Exception;

use Exception;

class InvalidModuleException extends Exception
{
    public function __construct(string $module, array $modulesConfig)
    {
        parent::__construct(sprintf("Invalid module '%s', posible values: %s.\n\nCheck that you have configured all your collections in sfs_cms config.", $module, implode(', ', array_keys($modulesConfig))));
    }
}
