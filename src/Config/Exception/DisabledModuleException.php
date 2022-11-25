<?php

namespace Softspring\CmsBundle\Config\Exception;

class DisabledModuleException extends \Exception
{
    public function __construct(string $module)
    {
        parent::__construct(sprintf('Module "%s" is disabled', $module));
    }
}
