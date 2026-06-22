<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Utils;

final class CacheTtlFormatter
{
    public static function format(mixed $cacheTtl): ?string
    {
        if (null === $cacheTtl || false === $cacheTtl || '' === $cacheTtl) {
            return null;
        }

        $cacheTtl = (int) $cacheTtl;
        if ($cacheTtl <= 0) {
            return $cacheTtl.'s';
        }

        if (0 === $cacheTtl % 86400) {
            return intdiv($cacheTtl, 86400).'d';
        }

        if (0 === $cacheTtl % 3600) {
            return intdiv($cacheTtl, 3600).'h';
        }

        if ($cacheTtl >= 120 && 0 === $cacheTtl % 60) {
            return intdiv($cacheTtl, 60).'m';
        }

        return $cacheTtl.'s';
    }
}
