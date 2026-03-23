<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Types;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, VecType, OptionType, TupleType, StructType, EnumType, ResultType, U8, U32, BoolType};

class CompoundTypeTest extends TestCase
{
    private TypeRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new TypeRegistry();
    }

    // ==================== Vec Tests ====================

    public function testVecEncodeEmpty(): void
    {
        $vec = new VecType($this->registry);
        $u8 = new U8($this->registry);
        $vec->setElementType($u8);

        $result = $vec->encode([]);
        $this->assertEquals('0x00', $result->toHex());
    }

    public function testVecEncodeU8(): void
    {
        $vec = new VecType($this->registry);
        $u8 = new U8($this->registry);
        $vec->setElementType($u8);

        $result = $vec->encode([1, 2, 3]);
        // Compact(3) = 0x0c, then 0x01, 0x02, 0x03
        $this->assertEquals('0x0c010203', $result->toHex());
    }

    public function testVecDecodeU8(): void
    {
        $vec = new VecType($this->registry);
        $u8 = new U8($this->registry);
        $vec->setElementType($u8);

        $bytes = ScaleBytes::fromHex('0x0c010203');
        $result = $vec->decode($bytes);

        $this->assertEquals([1, 2, 3], $result);
    }

    public function testVecRoundTrip(): void
    {
        $vec = new VecType($this->registry);
        $u32 = new U32($this->registry);
        $vec->setElementType($u32);

        $values = [0, 100, 1000, 10000];
        $encoded = $vec->encode($values);
        $decoded = $vec->decode(ScaleBytes::fromBytes($encoded->toBytes()));

        $this->assertEquals($values, $decoded);
    }

    // ==================== Option Tests ====================

    public function testOptionEncodeNone(): void
    {
        $option = new OptionType($this->registry);
        $u8 = new U8($this->registry);
        $option->setInnerType($u8);

        $result = $option->encode(null);
        $this->assertEquals('0x00', $result->toHex());
    }

    public function testOptionEncodeSome(): void
    {
        $option = new OptionType($this->registry);
        $u8 = new U8($this->registry);
        $option->setInnerType($u8);

        $result = $option->encode(42);
        $this->assertEquals('0x012a', $result->toHex());
    }

    public function testOptionDecodeNone(): void
    {
        $option = new OptionType($this->registry);
        $u8 = new U8($this->registry);
        $option->setInnerType($u8);

        $bytes = ScaleBytes::fromHex('0x00');
        $result = $option->decode($bytes);

        $this->assertNull($result);
    }

    public function testOptionDecodeSome(): void
    {
        $option = new OptionType($this->registry);
        $u8 = new U8($this->registry);
        $option->setInnerType($u8);

        $bytes = ScaleBytes::fromHex('0x012a');
        $result = $option->decode($bytes);

        $this->assertEquals(42, $result);
    }

    public function testOptionRoundTrip(): void
    {
        $option = new OptionType($this->registry);
        $u32 = new U32($this->registry);
        $option->setInnerType($u32);

        // Test Some
        $encoded = $option->encode(12345);
        $decoded = $option->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(12345, $decoded);

        // Test None
        $encoded = $option->encode(null);
        $decoded = $option->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertNull($decoded);
    }

    // ==================== Tuple Tests ====================

    public function testTupleEncode(): void
    {
        $tuple = new TupleType($this->registry);
        $u8 = new U8($this->registry);
        $bool = new BoolType($this->registry);
        $tuple->setElementTypes([$u8, $bool]);

        $result = $tuple->encode([42, true]);
        $this->assertEquals('0x2a01', $result->toHex());
    }

    public function testTupleDecode(): void
    {
        $tuple = new TupleType($this->registry);
        $u8 = new U8($this->registry);
        $bool = new BoolType($this->registry);
        $tuple->setElementTypes([$u8, $bool]);

        $bytes = ScaleBytes::fromHex('0x2a01');
        $result = $tuple->decode($bytes);

        $this->assertEquals([42, true], $result);
    }

    public function testTupleRoundTrip(): void
    {
        $tuple = new TupleType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);
        $bool = new BoolType($this->registry);
        $tuple->setElementTypes([$u8, $u32, $bool]);

        $values = [100, 100000, false];
        $encoded = $tuple->encode($values);
        $decoded = $tuple->decode(ScaleBytes::fromBytes($encoded->toBytes()));

        $this->assertEquals($values, $decoded);
    }

    // ==================== Struct Tests ====================

    public function testStructEncode(): void
    {
        $struct = new StructType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);
        $struct->setFields(['a' => $u8, 'b' => $u32]);

        $result = $struct->encode(['a' => 10, 'b' => 1000]);
        // U8(10) = 0x0a, U32(1000) = 0xe8030000
        $this->assertEquals('0x0ae8030000', $result->toHex());
    }

    public function testStructDecode(): void
    {
        $struct = new StructType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);
        $struct->setFields(['a' => $u8, 'b' => $u32]);

        $bytes = ScaleBytes::fromHex('0x0ae8030000');
        $result = $struct->decode($bytes);

        $this->assertEquals(['a' => 10, 'b' => 1000], $result);
    }

    public function testStructRoundTrip(): void
    {
        $struct = new StructType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);
        $bool = new BoolType($this->registry);
        $struct->setFields(['x' => $u8, 'y' => $u32, 'flag' => $bool]);

        $values = ['x' => 255, 'y' => 999999, 'flag' => true];
        $encoded = $struct->encode($values);
        $decoded = $struct->decode(ScaleBytes::fromBytes($encoded->toBytes()));

        $this->assertEquals($values, $decoded);
    }

    // ==================== Enum Tests ====================

    public function testEnumEncodeUnitVariant(): void
    {
        $enum = new EnumType($this->registry);
        $enum->addVariant('A', 0);
        $enum->addVariant('B', 1);
        $enum->addVariant('C', 2);

        $result = $enum->encode(['B' => null]);
        $this->assertEquals('0x01', $result->toHex());
    }

    public function testEnumDecodeUnitVariant(): void
    {
        $enum = new EnumType($this->registry);
        $enum->addVariant('A', 0);
        $enum->addVariant('B', 1);
        $enum->addVariant('C', 2);

        $bytes = ScaleBytes::fromHex('0x02');
        $result = $enum->decode($bytes);

        $this->assertEquals(['C' => null], $result);
    }

    public function testEnumEncodeWithData(): void
    {
        $enum = new EnumType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);
        $enum->addVariant('None', 0);
        $enum->addVariant('U8', 1, $u8);
        $enum->addVariant('U32', 2, $u32);

        $result = $enum->encode(['U8' => 100]);
        $this->assertEquals('0x0164', $result->toHex());
    }

    public function testEnumDecodeWithData(): void
    {
        $enum = new EnumType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);
        $enum->addVariant('None', 0);
        $enum->addVariant('U8', 1, $u8);
        $enum->addVariant('U32', 2, $u32);

        $bytes = ScaleBytes::fromHex('0x02e8030000');
        $result = $enum->decode($bytes);

        $this->assertEquals(['U32' => 1000], $result);
    }

    // ==================== Result Tests ====================

    public function testResultEncodeOk(): void
    {
        $result = new ResultType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);
        $result->setTypes($u8, $u32);

        $encoded = $result->encode(['Ok' => 42]);
        $this->assertEquals('0x002a', $encoded->toHex());
    }

    public function testResultEncodeErr(): void
    {
        $result = new ResultType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);
        $result->setTypes($u8, $u32);

        $encoded = $result->encode(['Err' => 1000]);
        $this->assertEquals('0x01e8030000', $encoded->toHex());
    }

    public function testResultDecodeOk(): void
    {
        $result = new ResultType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);
        $result->setTypes($u8, $u32);

        $bytes = ScaleBytes::fromHex('0x002a');
        $decoded = $result->decode($bytes);

        $this->assertEquals(['Ok' => 42], $decoded);
    }

    public function testResultDecodeErr(): void
    {
        $result = new ResultType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);
        $result->setTypes($u8, $u32);

        $bytes = ScaleBytes::fromHex('0x01e8030000');
        $decoded = $result->decode($bytes);

        $this->assertEquals(['Err' => 1000], $decoded);
    }

    public function testResultRoundTrip(): void
    {
        $result = new ResultType($this->registry);
        $u32 = new U32($this->registry);
        $result->setTypes($u32, $u32);

        // Test Ok
        $encoded = $result->encode(['Ok' => 12345]);
        $decoded = $result->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(['Ok' => 12345], $decoded);

        // Test Err
        $encoded = $result->encode(['Err' => 67890]);
        $decoded = $result->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(['Err' => 67890], $decoded);
    }
}
