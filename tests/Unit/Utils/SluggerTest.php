<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Utils;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Utils\Slugger;

class SluggerTest extends TestCase
{
    public function testItCreatesAsciiSlugs(): void
    {
        self::assertSame('Hola-Mundo', Slugger::slug('Hola Mundo'));
    }

    public function testItCreatesLowercaseSlugs(): void
    {
        self::assertSame('hola-mundo', Slugger::lowerSlug('Hola Mundo'));
    }
}
