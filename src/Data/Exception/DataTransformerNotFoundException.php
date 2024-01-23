<?php

namespace Softspring\CmsBundle\Data\Exception;

class DataTransformerNotFoundException extends \Exception
{
    public function __construct(string $type, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct("Data transformer not found for '$type' elements", $code, $previous);
    }
}
