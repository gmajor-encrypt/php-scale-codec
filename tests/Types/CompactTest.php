<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Types;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, Compact};

class CompactTest extends TestCase
{
    private TypeRegistry $registry;
    private Compact $compact;

    protected function setUp(): void
    {
        $this->registry = new TypeRegistry();
        $this->compact = new Compact($this->registry);
    }

    // Single byte mode tests (0-63)
    public function testEncodeZero(): void
    {
        $result = $this->compact->encode(0);
        $this->assertEquals('0x00', $result->toHex());
    }

    public function testEncodeSingleByteMode(): void
    {
        // 63 should be encoded as 0xFC (63 << 2 | 0b00)
        $result = $this->compact->encode(63);
        $this->assertEquals('0xfc', $result->toHex());
    }

    public function testDecodeZero(): void
    {
        $bytes = ScaleBytes::fromHex('0x00');
        $result = $this->compact->decode($bytes);
        $this->assertEquals(0, $result);
    }

    public function testDecodeSingleByteMode(): void
    {
        // 0xFC = 252 = 63 << 2, so value is 63
        $bytes = ScaleBytes::fromHex('0xfc');
        $result = $this->compact->decode($bytes);
        $this->assertEquals(63, $result);
    }

    // Two byte mode tests (64-16383)
    public function testEncodeTwoByteMode(): void
    {
        // 64 should be encoded as 0x0101 (64 << 2 | 0b01 = 257)
        $result = $this->compact->encode(64);
        $this->assertEquals('0x0101', $result->toHex());
    }

    public function testEncodeTwoByteModeMax(): void
    {
        // 16383 should be encoded as two bytes
        $result = $this->compact->encode(16383);
        $this->assertEquals(2, strlen($result->toBytes()));
    }

    public function testDecodeTwoByteMode(): void
    {
        // 0x0101 = 257 = 64 << 2 | 1, so value is 64
        $bytes = ScaleBytes::fromHex('0x0101');
        $result = $this->compact->decode($bytes);
        $this->assertEquals(64, $result);
    }

    // Four byte mode tests (16384-1073741823)
    public function testEncodeFourByteMode(): void
    {
        // 16384 should be encoded as four bytes
        $result = $this->compact->encode(16384);
        $this->assertEquals(4, strlen($result->toBytes()));
    }

    public function testEncodeFourByteModeMax(): void
    {
        // 1073741823 should be encoded as four bytes
        $result = $this->compact->encode(1073741823);
        $this->assertEquals(4, strlen($result->toBytes()));
    }

    public function testDecodeFourByteMode(): void
    {
        // 0x02000100 = 33554432 << 2 | 2, but let's use a simpler example
        // 16384 << 2 = 65536, | 2 = 65538 = 0x010002
        // Actually: 16384 in compact = 0x02000100
        $result = $this->compact->encode(16384);
        $decoded = $this->compact->decode(ScaleBytes::fromBytes($result->toBytes()));
        $this->assertEquals(16384, $decoded);
    }

    // Big integer mode tests
    public function testEncodeBigInt(): void
    {
        // Value > 1073741823 uses big integer mode
        $value = 1073741824; // Just above four byte max
        $result = $this->compact->encode($value);
        
        // First byte should have 0b03 mode
        $firstByte = $result->toBytes()[0];
        $this->assertEquals(0x03, $firstByte & 0x03);
    }

    public function testEncodeBigIntWithString(): void
    {
        // Test with string input for very large values
        $value = '340282366920938463463374607431768211455'; // U128 max
        $result = $this->compact->encode($value);
        
        $decoded = $this->compact->decode(ScaleBytes::fromBytes($result->toBytes()));
        $this->assertEquals($value, (string) $decoded);
    }

    public function testDecodeBigInt(): void
    {
        // Encode a large value and decode it
        $value = 10000000000; // 10 billion
        $encoded = $this->compact->encode($value);
        $decoded = $this->compact->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals($value, $decoded);
    }

    // Round-trip tests
    public function testRoundTripSmallValues(): void
    {
        $values = [0, 1, 63, 64, 100, 16383, 16384, 100000, 1073741823];
        
        foreach ($values as $value) {
            $encoded = $this->compact->encode($value);
            $decoded = $this->compact->decode(ScaleBytes::fromBytes($encoded->toBytes()));
            $this->assertEquals($value, $decoded, "Round-trip failed for value $value");
        }
    }

    public function testRoundTripBigInt(): void
    {
        $values = [
            '1073741824',
            '10000000000',
            '340282366920938463463374607431768211455', // U128 max
        ];
        
        foreach ($values as $value) {
            $encoded = $this->compact->encode($value);
            $decoded = $this->compact->decode(ScaleBytes::fromBytes($encoded->toBytes()));
            $this->assertEquals($value, (string) $decoded, "Round-trip failed for value $value");
        }
    }

    // Validation tests
    public function testIsValid(): void
    {
        $this->assertTrue($this->compact->isValid(0));
        $this->assertTrue($this->compact->isValid(100));
        $this->assertTrue($this->compact->isValid('123456789'));
        $this->assertFalse($this->compact->isValid(-1));
        $this->assertFalse($this->compact->isValid(''));
        $this->assertFalse($this->compact->isValid('abc'));
    }

    public function testEncodeNegativeThrows(): void
    {
        $this->expectException(ScaleEncodeException::class);
        $this->compact->encode(-1);
    }

    public function testEncodeInvalidTypeThrows(): void
    {
        $this->expectException(ScaleEncodeException::class);
        $this->compact->encode([]); // Array is invalid
    }

    // Edge cases
    public function testEncodeBoundaryValues(): void
    {
        // Test exact boundaries
        $boundaries = [0, 63, 64, 16383, 16384, 1073741823, 1073741824];
        
        foreach ($boundaries as $value) {
            $encoded = $this->compact->encode($value);
            $decoded = $this->compact->decode(ScaleBytes::fromBytes($encoded->toBytes()));
            $this->assertEquals($value, $decoded);
        }
    }

    public function testGetType(): void
    {
        $this->assertEquals('Compact', $this->compact->getTypeName());
    }
}