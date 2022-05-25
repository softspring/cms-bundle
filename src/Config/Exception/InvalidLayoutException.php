<?php

namespace Softspring\CmsBundle\Config\Exception;

class InvalidLayoutException extends \Exception
{
    public function __construct(string $layout, array $layoutsConfig)
    {
        parent::__construct(sprintf('Invalid layout "%s", posible values: %s', $layout, implode(', ', array_keys($layoutsConfig))));
    }
}
