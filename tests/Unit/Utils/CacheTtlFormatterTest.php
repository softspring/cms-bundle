<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Utils;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Utils\CacheTtlFormatter;

class CacheTtlFormatterTest extends TestCase
{
    public function testItFormatsCacheTtls(): void
    {
        self::assertNull(CacheTtlFormatter::format(null));
        self::assertNull(CacheTtlFormatter::format(false));
        self::assertSame('0s', CacheTtlFormatter::format(0));
        self::assertSame('60s', CacheTtlFormatter::format(60));
        self::assertSame('90s', CacheTtlFormatter::format(90));
        self::assertSame('30m', CacheTtlFormatter::format(1800));
        self::assertSame('2h', CacheTtlFormatter::format(7200));
        self::assertSame('4d', CacheTtlFormatter::format(345600));
        self::assertSame('30d', CacheTtlFormatter::format(2592000));
    }
}
