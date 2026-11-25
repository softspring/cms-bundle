<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Utils;

use Stringable;

/**
 * Prepares the parameters that the CMS renderers pass to url(), path() and controller().
 *
 * Null values are removed and Stringable objects (for example a site entity) are converted to strings,
 * because URL generation and fragment controller references only accept scalars and arrays.
 */
final class RenderParams
{
    public static function normalize(array $params): array
    {
        $normalized = [];

        foreach ($params as $key => $value) {
            if (null === $value) {
                continue;
            }

            if ($value instanceof Stringable) {
                $value = (string) $value;
            } elseif (is_array($value)) {
                $value = self::normalize($value);
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
