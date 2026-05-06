<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Render\Exception;

use Exception;
use Throwable;

class IsolatedEnvironmentException extends Exception
{
    public function __construct(string $class, string $method, ?Throwable $previous = null)
    {
        $message = sprintf('The method "%s::%s" is not allowed to be called in the isolated environment.', $class, $method);
        parent::__construct($message, 0, $previous);
    }
}
