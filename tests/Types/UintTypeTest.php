<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Types;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, U8, U16, U32, U64, U128};

class UintTypeTest extends TestCase
{
    private TypeRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new TypeRegistry();
    }

    // U8 Tests
    public function testU8EncodeZero(): void
    {
        $u8 = new U8($this->registry);
        $result = $u8->encode(0);
        $this->assertEquals('0x00', $result->toHex());
    }

    public function testU8EncodeMax(): void
    {
        $u8 = new U8($this->registry);
        $result = $u8->encode(255);
        $this->assertEquals('0xff', $result->toHex());
    }

    public function testU8EncodeMid(): void
    {
        $u8 = new U8($this->registry);
        $result = $u8->encode(100);
        $this->assertEquals('0x64', $result->toHex());
    }

    public function testU8DecodeZero(): void
    {
        $u8 = new U8($this->registry);
        $bytes = ScaleBytes::fromHex('0x00');
        $result = $u8->decode($bytes);
        $this->assertEquals(0, $result);
    }

    public function testU8DecodeMax(): void
    {
        $u8 = new U8($this->registry);
        $bytes = ScaleBytes::fromHex('0xff');
        $result = $u8->decode($bytes);
        $this->assertEquals(255, $result);
    }

    public function testU8EncodeNegativeThrows(): void
    {
        $this->expectException(\Substrate\ScaleCodec\Exception\ScaleEncodeException::class);
        $u8 = new U8($this->registry);
        $u8->encode(-1);
    }

    public function testU8EncodeOverflowThrows(): void
    {
        $this->expectException(\Substrate\ScaleCodec\Exception\ScaleEncodeException::class);
        $u8 = new U8($this->registry);
        $u8->encode(256);
    }

    // U16 Tests
    public function testU16EncodeZero(): void
    {
        $u16 = new U16($this->registry);
        $result = $u16->encode(0);
        $this->assertEquals('0x0000', $result->toHex());
    }

    public function testU16EncodeMax(): void
    {
        $u16 = new U16($this->registry);
        $result = $u16->encode(65535);
        $this->assertEquals('0xffff', $result->toHex());
    }

    public function testU16EncodeLittleEndian(): void
    {
        $u16 = new U16($this->registry);
        $result = $u16->encode(0x0102);
        $this->assertEquals('0x0201', $result->toHex());
    }

    public function testU16DecodeLittleEndian(): void
    {
        $u16 = new U16($this->registry);
        $bytes = ScaleBytes::fromHex('0x0201');
        $result = $u16->decode($bytes);
        $this->assertEquals(0x0102, $result);
    }

    // U32 Tests
    public function testU32EncodeZero(): void
    {
        $u32 = new U32($this->registry);
        $result = $u32->encode(0);
        $this->assertEquals('0x00000000', $result->toHex());
    }

    public function testU32EncodeMax(): void
    {
        $u32 = new U32($this->registry);
        $result = $u32->encode(4294967295);
        $this->assertEquals('0xffffffff', $result->toHex());
    }

    public function testU32EncodeLittleEndian(): void
    {
        $u32 = new U32($this->registry);
        $result = $u32->encode(0x01020304);
        $this->assertEquals('0x04030201', $result->toHex());
    }

    public function testU32DecodeLittleEndian(): void
    {
        $u32 = new U32($this->registry);
        $bytes = ScaleBytes::fromHex('0x04030201');
        $result = $u32->decode($bytes);
        $this->assertEquals(0x01020304, $result);
    }

    // U64 Tests
    public function testU64EncodeZero(): void
    {
        $u64 = new U64($this->registry);
        $result = $u64->encode(0);
        $this->assertEquals('0x0000000000000000', $result->toHex());
    }

    public function testU64EncodeLargeValue(): void
    {
        $u64 = new U64($this->registry);
        $result = $u64->encode(0x0102030405060708);
        $this->assertEquals('0x0807060504030201', $result->toHex());
    }

    // U128 Tests
    public function testU128EncodeZero(): void
    {
        $u128 = new U128($this->registry);
        $result = $u128->encode(0);
        $this->assertEquals('0x00000000000000000000000000000000', $result->toHex());
    }

    public function testU128EncodeWithString(): void
    {
        $u128 = new U128($this->registry);
        $result = $u128->encode('340282366920938463463374607431768211455');
        $this->assertEquals('0xffffffffffffffffffffffffffffffff', $result->toHex());
    }

    public function testU128DecodeMax(): void
    {
        $u128 = new U128($this->registry);
        $bytes = ScaleBytes::fromHex('0xffffffffffffffffffffffffffffffff');
        $result = $u128->decode($bytes);
        $this->assertEquals('340282366920938463463374607431768211455', $result);
    }

    // Round-trip tests
    public function testU8RoundTrip(): void
    {
        $u8 = new U8($this->registry);
        for ($i = 0; $i <= 255; $i += 25) {
            $encoded = $u8->encode($i);
            $decoded = $u8->decode(ScaleBytes::fromBytes($encoded->toBytes()));
            $this->assertEquals($i, $decoded);
        }
    }

    public function testU32RoundTrip(): void
    {
        $u32 = new U32($this->registry);
        $values = [0, 1, 255, 256, 65535, 65536, 4294967295];
        foreach ($values as $value) {
            $encoded = $u32->encode($value);
            $decoded = $u32->decode(ScaleBytes::fromBytes($encoded->toBytes()));
            $this->assertEquals($value, $decoded);
        }
    }

    // IsValid tests
    public function testU8IsValid(): void
    {
        $u8 = new U8($this->registry);
        $this->assertTrue($u8->isValid(0));
        $this->assertTrue($u8->isValid(255));
        $this->assertFalse($u8->isValid(-1));
        $this->assertFalse($u8->isValid(256));
        $this->assertFalse($u8->isValid('string'));
    }

    public function testU32IsValid(): void
    {
        $u32 = new U32($this->registry);
        $this->assertTrue($u32->isValid(0));
        $this->assertTrue($u32->isValid(4294967295));
        $this->assertFalse($u32->isValid(-1));
        $this->assertFalse($u32->isValid(4294967296));
    }
}
