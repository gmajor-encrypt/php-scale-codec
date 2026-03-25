<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Exception\{ScaleEncodeException, ScaleDecodeException, InvalidTypeException};

class ExceptionTest extends TestCase
{
    // ==================== ScaleEncodeException Tests ====================

    public function testInvalidTypeException(): void
    {
        $exception = ScaleEncodeException::invalidType('U8', 'string');
        $this->assertInstanceOf(ScaleEncodeException::class, $exception);
        $this->assertStringContainsString('U8', $exception->getMessage());
    }

    public function testOutOfRangeException(): void
    {
        $exception = ScaleEncodeException::outOfRange('U8', 300, '0-255');
        $this->assertInstanceOf(ScaleEncodeException::class, $exception);
        $this->assertStringContainsString('300', $exception->getMessage());
    }

    // ==================== ScaleDecodeException Tests ====================

    public function testInvalidBoolValue(): void
    {
        $exception = ScaleDecodeException::invalidBoolValue(5);
        $this->assertInstanceOf(ScaleDecodeException::class, $exception);
        $this->assertStringContainsString('5', $exception->getMessage());
    }

    public function testInvalidEnumVariant(): void
    {
        $exception = ScaleDecodeException::invalidEnumVariant(10, [0, 1, 2]);
        $this->assertInstanceOf(ScaleDecodeException::class, $exception);
        $this->assertStringContainsString('10', $exception->getMessage());
    }

    public function testUnexpectedEndOfData(): void
    {
        $exception = ScaleDecodeException::unexpectedEndOfData(10, 5);
        $this->assertInstanceOf(ScaleDecodeException::class, $exception);
        $this->assertStringContainsString('10', $exception->getMessage());
    }

    // ==================== InvalidTypeException Tests ====================

    public function testNotRegistered(): void
    {
        $exception = InvalidTypeException::notRegistered('CustomType');
        $this->assertInstanceOf(InvalidTypeException::class, $exception);
        $this->assertStringContainsString('CustomType', $exception->getMessage());
    }

    public function testInvalidFormat(): void
    {
        $exception = InvalidTypeException::invalidFormat('Vec', 'Expected type parameter');
        $this->assertInstanceOf(InvalidTypeException::class, $exception);
        $this->assertStringContainsString('Vec', $exception->getMessage());
    }
}
