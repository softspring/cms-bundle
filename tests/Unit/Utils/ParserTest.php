<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Utils;

use PHPUnit\Framework\TestCase;
use Softspring\CmsBundle\Utils\Parser;

class ParserTest extends TestCase
{
    public function testItBuildsJavascriptLikeParamsString(): void
    {
        self::assertSame(
            "'title':'Home','enabled':true,'hidden':false,'count':3",
            Parser::arrayToParamsString([
                'title' => 'Home',
                'enabled' => true,
                'hidden' => false,
                'empty' => null,
                'count' => 3,
            ]),
        );
    }
}
