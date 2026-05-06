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

        $data = array_map(function ($k, mixed $v): string {
            if (is_bool($v)) {
                return "'$k':".($v ? 'true' : 'false');
            }

            return is_string($v) ? "'$k':'$v'" : "'$k':$v";
        }, array_keys($data), array_values($data));

        return implode(',', $data);
    }
}
