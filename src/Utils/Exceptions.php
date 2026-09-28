<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Utils;

use Throwable;

class Exceptions
{
    public static function toArray(Throwable $e): array
    {
        return [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()),
            'previous' => $e->getPrevious() instanceof Throwable ? self::toArray($e->getPrevious()) : null,
        ];
    }
}
