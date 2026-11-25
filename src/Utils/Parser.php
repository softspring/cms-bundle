<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Utils;

class Parser
{
    public static function arrayToParamsString(array $data): string
    {
        $data = array_filter($data, function ($v): bool {
            return null !== $v;
        });

        $data = array_map(function (int|string $k, mixed $v): string {
            $k = self::escapeString((string) $k);

            if (is_bool($v)) {
                return "'$k':".($v ? 'true' : 'false');
            }

            return is_string($v) ? "'$k':'".self::escapeString($v)."'" : "'$k':$v";
        }, array_keys($data), array_values($data));

        return implode(',', $data);
    }

    /**
     * Escapes a value for a single-quoted Twig string literal.
     */
    private static function escapeString(string $value): string
    {
        return addcslashes($value, "\\'");
    }
}
