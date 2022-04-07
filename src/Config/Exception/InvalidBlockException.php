<?php

namespace Softspring\CmsBundle\Config\Exception;

class InvalidBlockException extends \Exception
{
    public function __construct(string $block, array $blocksConfig)
    {
        parent::__construct(sprintf('Invalid block "%s", posible values: %s', $block, implode(', ', array_keys($blocksConfig))));
    }
}