<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Serialization;

use function get_debug_type;
use function is_array;
use function is_bool;
use function is_int;
use function is_scalar;
use function is_string;
use function sprintf;
use function str_contains;
use function strtolower;

class SensitiveValueSanitizer
{
    private const SENSITIVE_KEY_PARTS = ['api_key', 'apikey', 'access_token', 'auth_token', 'secret', 'password', 'private_key', 'credential'];

    public function sanitize(mixed $value, ?string $key = null): mixed
    {
        if (null !== $key && $this->isSensitiveKey($key)) {
            return '[redacted]';
        }

        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $childKey => $childValue) {
                $sanitized[$childKey] = $this->sanitize($childValue, (string) $childKey);
            }

            return $sanitized;
        }

        if (is_string($value) || is_int($value) || is_bool($value) || null === $value) {
            return $value;
        }

        if (is_scalar($value)) {
            return $value;
        }

        return sprintf('[%s]', get_debug_type($value));
    }

    private function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);

        foreach (self::SENSITIVE_KEY_PARTS as $part) {
            if (str_contains($key, $part)) {
                return true;
            }
        }

        return false;
    }
}
