<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Types;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, I8, I16, I32, I64, I128};

class IntTypeTest extends TestCase
{
    private TypeRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new TypeRegistry();
    }

    // I8 Tests
    public function testI8EncodeZero(): void
    {
        $i8 = new I8($this->registry);
        $result = $i8->encode(0);
        $this->assertEquals('0x00', $result->toHex());
    }

    public function testI8EncodePositive(): void
    {
        $i8 = new I8($this->registry);
        $result = $i8->encode(127);
        $this->assertEquals('0x7f', $result->toHex());
    }

    public function testI8EncodeNegative(): void
    {
        $i8 = new I8($this->registry);
        $result = $i8->encode(-1);
        $this->assertEquals('0xff', $result->toHex());
    }

    public function testI8EncodeMin(): void
    {
        $i8 = new I8($this->registry);
        $result = $i8->encode(-128);
        $this->assertEquals('0x80', $result->toHex());
    }

    public function testI8DecodeZero(): void
    {
        $i8 = new I8($this->registry);
        $bytes = ScaleBytes::fromHex('0x00');
        $result = $i8->decode($bytes);
        $this->assertEquals(0, $result);
    }

    public function testI8DecodePositive(): void
    {
        $i8 = new I8($this->registry);
        $bytes = ScaleBytes::fromHex('0x7f');
        $result = $i8->decode($bytes);
        $this->assertEquals(127, $result);
    }

    public function testI8DecodeNegative(): void
    {
        $i8 = new I8($this->registry);
        $bytes = ScaleBytes::fromHex('0xff');
        $result = $i8->decode($bytes);
        $this->assertEquals(-1, $result);
    }

    public function testI8DecodeMin(): void
    {
        $i8 = new I8($this->registry);
        $bytes = ScaleBytes::fromHex('0x80');
        $result = $i8->decode($bytes);
        $this->assertEquals(-128, $result);
    }

    public function testI8EncodeOverflowThrows(): void
    {
        $this->expectException(\Substrate\ScaleCodec\Exception\ScaleEncodeException::class);
        $i8 = new I8($this->registry);
        $i8->encode(128);
    }

    public function testI8EncodeUnderflowThrows(): void
    {
        $this->expectException(\Substrate\ScaleCodec\Exception\ScaleEncodeException::class);
        $i8 = new I8($this->registry);
        $i8->encode(-129);
    }

    // I16 Tests
    public function testI16EncodeZero(): void
    {
        $i16 = new I16($this->registry);
        $result = $i16->encode(0);
        $this->assertEquals('0x0000', $result->toHex());
    }

    public function testI16EncodePositive(): void
    {
        $i16 = new I16($this->registry);
        $result = $i16->encode(32767);
        $this->assertEquals('0xff7f', $result->toHex());
    }

    public function testI16EncodeNegative(): void
    {
        $i16 = new I16($this->registry);
        $result = $i16->encode(-1);
        $this->assertEquals('0xffff', $result->toHex());
    }

    public function testI16EncodeMin(): void
    {
        $i16 = new I16($this->registry);
        $result = $i16->encode(-32768);
        $this->assertEquals('0x0080', $result->toHex());
    }

    public function testI16DecodePositive(): void
    {
        $i16 = new I16($this->registry);
        $bytes = ScaleBytes::fromHex('0xff7f');
        $result = $i16->decode($bytes);
        $this->assertEquals(32767, $result);
    }

    public function testI16DecodeNegative(): void
    {
        $i16 = new I16($this->registry);
        $bytes = ScaleBytes::fromHex('0xffff');
        $result = $i16->decode($bytes);
        $this->assertEquals(-1, $result);
    }

    // I32 Tests
    public function testI32EncodeZero(): void
    {
        $i32 = new I32($this->registry);
        $result = $i32->encode(0);
        $this->assertEquals('0x00000000', $result->toHex());
    }

    public function testI32EncodePositive(): void
    {
        $i32 = new I32($this->registry);
        $result = $i32->encode(2147483647);
        $this->assertEquals('0xffffff7f', $result->toHex());
    }

    public function testI32EncodeNegative(): void
    {
        $i32 = new I32($this->registry);
        $result = $i32->encode(-1);
        $this->assertEquals('0xffffffff', $result->toHex());
    }

    public function testI32EncodeMin(): void
    {
        $i32 = new I32($this->registry);
        $result = $i32->encode(-2147483648);
        $this->assertEquals('0x00000080', $result->toHex());
    }

    public function testI32DecodePositive(): void
    {
        $i32 = new I32($this->registry);
        $bytes = ScaleBytes::fromHex('0xffffff7f');
        $result = $i32->decode($bytes);
        $this->assertEquals(2147483647, $result);
    }

    public function testI32DecodeNegative(): void
    {
        $i32 = new I32($this->registry);
        $bytes = ScaleBytes::fromHex('0xffffffff');
        $result = $i32->decode($bytes);
        $this->assertEquals(-1, $result);
    }

    // I64 Tests
    public function testI64EncodeZero(): void
    {
        $i64 = new I64($this->registry);
        $result = $i64->encode(0);
        $this->assertEquals('0x0000000000000000', $result->toHex());
    }

    public function testI64EncodeNegative(): void
    {
        $i64 = new I64($this->registry);
        $result = $i64->encode(-1);
        $this->assertEquals('0xffffffffffffffff', $result->toHex());
    }

    public function testI64EncodeWithString(): void
    {
        $i64 = new I64($this->registry);
        $result = $i64->encode('9223372036854775807');
        $this->assertEquals('0xffffffffffffff7f', $result->toHex());
    }

    // I128 Tests
    public function testI128EncodeZero(): void
    {
        $i128 = new I128($this->registry);
        $result = $i128->encode(0);
        $this->assertEquals('0x00000000000000000000000000000000', $result->toHex());
    }

    public function testI128EncodeNegative(): void
    {
        $i128 = new I128($this->registry);
        $result = $i128->encode(-1);
        $this->assertEquals('0xffffffffffffffffffffffffffffffff', $result->toHex());
    }

    // Round-trip tests
    public function testI8RoundTrip(): void
    {
        $i8 = new I8($this->registry);
        $values = [-128, -1, 0, 1, 127];
        foreach ($values as $value) {
            $encoded = $i8->encode($value);
            $decoded = $i8->decode(ScaleBytes::fromBytes($encoded->toBytes()));
            $this->assertEquals($value, $decoded);
        }
    }

    public function testI32RoundTrip(): void
    {
        $i32 = new I32($this->registry);
        $values = [-2147483648, -1, 0, 1, 2147483647];
        foreach ($values as $value) {
            $encoded = $i32->encode($value);
            $decoded = $i32->decode(ScaleBytes::fromBytes($encoded->toBytes()));
            $this->assertEquals($value, $decoded);
        }
    }

    // IsValid tests
    public function testI8IsValid(): void
    {
        $i8 = new I8($this->registry);
        $this->assertTrue($i8->isValid(0));
        $this->assertTrue($i8->isValid(127));
        $this->assertTrue($i8->isValid(-128));
        $this->assertFalse($i8->isValid(128));
        $this->assertFalse($i8->isValid(-129));
    }

    public function testI32IsValid(): void
    {
        $i32 = new I32($this->registry);
        $this->assertTrue($i32->isValid(0));
        $this->assertTrue($i32->isValid(2147483647));
        $this->assertTrue($i32->isValid(-2147483648));
        $this->assertFalse($i32->isValid(2147483648));
        $this->assertFalse($i32->isValid(-2147483649));
    }
}
