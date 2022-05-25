<?php

namespace Softspring\CmsBundle\Config\Exception;

class InvalidModuleException extends \Exception
{
    public function __construct(string $module, array $modulesConfig)
    {
        parent::__construct(sprintf('Invalid module "%s", posible values: %s', $module, implode(', ', array_keys($modulesConfig))));
    }
}
