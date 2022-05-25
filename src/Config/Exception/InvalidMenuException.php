<?php

namespace Softspring\CmsBundle\Config\Exception;

class InvalidMenuException extends \Exception
{
    public function __construct(string $menu, array $menusConfig)
    {
        parent::__construct(sprintf('Invalid menu "%s", posible values: %s', $menu, implode(', ', array_keys($menusConfig))));
    }
}
