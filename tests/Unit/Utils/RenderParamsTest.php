<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Utils;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Utils\RenderParams;
use Stringable;

class RenderParamsTest extends TestCase
{
    public function testItRemovesNullsAndConvertsStringableObjects(): void
    {
        $site = new class implements Stringable {
            public function __toString(): string
            {
                return 'default';
            }
        };

        self::assertSame(
            [
                'title' => "Kid's",
                'enabled' => false,
                'count' => 0,
                '_site' => 'default',
                'texts' => ['en' => 'Hi', 'site' => 'default'],
            ],
            RenderParams::normalize([
                'title' => "Kid's",
                'enabled' => false,
                'count' => 0,
                'empty' => null,
                '_site' => $site,
                'texts' => ['en' => 'Hi', 'de' => null, 'site' => $site],
            ]),
        );
    }
}
