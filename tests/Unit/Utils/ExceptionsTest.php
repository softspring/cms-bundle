<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Test\Unit\Utils;

use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Softspring\CmsBundle\Utils\Exceptions;

class ExceptionsTest extends TestCase
{
    public function testItConvertsThrowableToArrayIncludingPreviousException(): void
    {
        $previous = new Exception('Previous error', 7);
        $exception = new RuntimeException('Current error', 9, $previous);

        $data = Exceptions::toArray($exception);

        self::assertSame(RuntimeException::class, $data['class']);
        self::assertSame('Current error', $data['message']);
        self::assertSame(9, $data['code']);
        self::assertIsString($data['file']);
        self::assertIsInt($data['line']);
        self::assertIsArray($data['trace']);
        self::assertSame(Exception::class, $data['previous']['class']);
        self::assertSame('Previous error', $data['previous']['message']);
        self::assertSame(7, $data['previous']['code']);
    }
}
