<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Types;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, U8, U16, U32, U64, U128, I8, I16, I32, I64, I128};
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

class IntegerBoundaryTest extends TestCase
{
    private TypeRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new TypeRegistry();
    }

    // ==================== U8 Boundary Tests ====================

    public function testU8MinValue(): void
    {
        $u8 = new U8($this->registry);
        $encoded = $u8->encode(0);
        $decoded = $u8->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(0, $decoded);
    }

    public function testU8MaxValue(): void
    {
        $u8 = new U8($this->registry);
        $encoded = $u8->encode(255);
        $decoded = $u8->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(255, $decoded);
    }

    public function testU8OverflowThrowsException(): void
    {
        $this->expectException(ScaleEncodeException::class);
        $u8 = new U8($this->registry);
        $u8->encode(256);
    }

    public function testU8NegativeThrowsException(): void
    {
        $this->expectException(ScaleEncodeException::class);
        $u8 = new U8($this->registry);
        $u8->encode(-1);
    }

    // ==================== U32 Boundary Tests ====================

    public function testU32MinValue(): void
    {
        $u32 = new U32($this->registry);
        $encoded = $u32->encode(0);
        $decoded = $u32->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(0, $decoded);
    }

    public function testU32MaxValue(): void
    {
        $u32 = new U32($this->registry);
        $max = 4294967295;
        $encoded = $u32->encode($max);
        $decoded = $u32->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals($max, $decoded);
    }

    public function testU32OverflowThrowsException(): void
    {
        $this->expectException(ScaleEncodeException::class);
        $u32 = new U32($this->registry);
        $u32->encode(4294967296);
    }

    // ==================== U64 Boundary Tests ====================

    public function testU64MinValue(): void
    {
        $u64 = new U64($this->registry);
        $encoded = $u64->encode('0');
        $decoded = $u64->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals('0', $decoded);
    }

    public function testU64MaxValue(): void
    {
        $u64 = new U64($this->registry);
        $max = '18446744073709551615';
        $encoded = $u64->encode($max);
        $decoded = $u64->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals($max, $decoded);
    }

    public function testU64StringInput(): void
    {
        $u64 = new U64($this->registry);
        $value = '1000000000000';
        $encoded = $u64->encode($value);
        $decoded = $u64->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals($value, $decoded);
    }

    // ==================== U128 Boundary Tests ====================

    public function testU128MinValue(): void
    {
        $u128 = new U128($this->registry);
        $encoded = $u128->encode('0');
        $decoded = $u128->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals('0', $decoded);
    }

    public function testU128MaxValue(): void
    {
        $u128 = new U128($this->registry);
        $max = '340282366920938463463374607431768211455';
        $encoded = $u128->encode($max);
        $decoded = $u128->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals($max, $decoded);
    }

    // ==================== I8 Boundary Tests ====================

    public function testI8MinValue(): void
    {
        $i8 = new I8($this->registry);
        $encoded = $i8->encode(-128);
        $decoded = $i8->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(-128, $decoded);
    }

    public function testI8MaxValue(): void
    {
        $i8 = new I8($this->registry);
        $encoded = $i8->encode(127);
        $decoded = $i8->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(127, $decoded);
    }

    public function testI8Zero(): void
    {
        $i8 = new I8($this->registry);
        $encoded = $i8->encode(0);
        $decoded = $i8->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(0, $decoded);
    }

    public function testI8OverflowThrowsException(): void
    {
        $this->expectException(ScaleEncodeException::class);
        $i8 = new I8($this->registry);
        $i8->encode(128);
    }

    public function testI8UnderflowThrowsException(): void
    {
        $this->expectException(ScaleEncodeException::class);
        $i8 = new I8($this->registry);
        $i8->encode(-129);
    }

    // ==================== I32 Boundary Tests ====================

    public function testI32MinValue(): void
    {
        $i32 = new I32($this->registry);
        $encoded = $i32->encode(-2147483648);
        $decoded = $i32->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(-2147483648, $decoded);
    }

    public function testI32MaxValue(): void
    {
        $i32 = new I32($this->registry);
        $encoded = $i32->encode(2147483647);
        $decoded = $i32->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(2147483647, $decoded);
    }

    // ==================== I64 Boundary Tests ====================

    public function testI64MinValue(): void
    {
        $i64 = new I64($this->registry);
        $min = '-9223372036854775808';
        $encoded = $i64->encode($min);
        $decoded = $i64->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals($min, $decoded);
    }

    public function testI64MaxValue(): void
    {
        $i64 = new I64($this->registry);
        $max = '9223372036854775807';
        $encoded = $i64->encode($max);
        $decoded = $i64->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals($max, $decoded);
    }

    // ==================== I128 Boundary Tests ====================

    public function testI128MinValue(): void
    {
        $i128 = new I128($this->registry);
        $min = '-170141183460469231731687303715884105728';
        $encoded = $i128->encode($min);
        $decoded = $i128->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals($min, $decoded);
    }

    public function testI128MaxValue(): void
    {
        $i128 = new I128($this->registry);
        $max = '170141183460469231731687303715884105727';
        $encoded = $i128->encode($max);
        $decoded = $i128->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals($max, $decoded);
    }

    // ==================== Invalid Input Tests ====================

    public function testInvalidTypeThrowsException(): void
    {
        $this->expectException(ScaleEncodeException::class);
        $u8 = new U8($this->registry);
        $u8->encode([]);
    }

    public function testNullThrowsException(): void
    {
        $this->expectException(ScaleEncodeException::class);
        $u8 = new U8($this->registry);
        $u8->encode(null);
    }

    // ==================== Validation Tests ====================

    public function testIsValidForU8(): void
    {
        $u8 = new U8($this->registry);
        $this->assertTrue($u8->isValid(0));
        $this->assertTrue($u8->isValid(255));
        $this->assertFalse($u8->isValid(-1));
        $this->assertFalse($u8->isValid(256));
    }

    public function testIsValidForI8(): void
    {
        $i8 = new I8($this->registry);
        $this->assertTrue($i8->isValid(-128));
        $this->assertTrue($i8->isValid(127));
        $this->assertTrue($i8->isValid(0));
        $this->assertFalse($i8->isValid(-129));
        $this->assertFalse($i8->isValid(128));
    }
}
