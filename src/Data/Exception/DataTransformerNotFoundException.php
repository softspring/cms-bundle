<?php

namespace Softspring\CmsBundle\Data\Exception;

use Exception;
use Throwable;

/**
 * @deprecated this class is deprecated, and will be removed on 6.0 version, when fixtures will be refactored to use serializer
 */
class DataTransformerNotFoundException extends Exception
{
    public function __construct(string $type, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("Data transformer not found for '$type' elements", $code, $previous);
    }
}
