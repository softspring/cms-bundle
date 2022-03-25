<?php

namespace Softspring\CmsBundle\Config\Exception;

class InvalidContentException extends \Exception
{
    public function __construct(string $content, array $contentsConfig)
    {
        parent::__construct(sprintf('Invalid content type "%s", posible values: %s', $content, implode(', ', array_keys($contentsConfig))));
    }
}