<?php

namespace Softspring\CmsBundle\EntityTransformer;

class UnsupportedException extends \Exception
{
    public function __construct(string $supportedClass, string $providedClass, \Throwable $previous = null)
    {
        $message = sprintf('This transformer requires %s object, %s provided', $supportedClass, $providedClass);
        parent::__construct($message, 0, $previous);
    }
}
