<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Serialization;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Serialization\SensitiveValueSanitizer;
use stdClass;

class SensitiveValueSanitizerTest extends TestCase
{
    public function testItRedactsSensitiveKeysRecursively(): void
    {
        $sanitizer = new SensitiveValueSanitizer();

        self::assertSame([
            'name' => 'production',
            'api_key' => '[redacted]',
            'nested' => [
                'password' => '[redacted]',
                'enabled' => true,
            ],
        ], $sanitizer->sanitize([
            'name' => 'production',
            'api_key' => 'secret-value',
            'nested' => [
                'password' => 'secret-password',
                'enabled' => true,
            ],
        ]));
    }

    public function testItReplacesObjectsWithDebugType(): void
    {
        $sanitizer = new SensitiveValueSanitizer();

        self::assertSame('[stdClass]', $sanitizer->sanitize(new stdClass()));
    }
}
