<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Types;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, Compact};
use Substrate\ScaleCodec\Exception\ScaleEncodeException;

class CompactBoundaryTest extends TestCase
{
    private TypeRegistry $registry;
    private Compact $compact;

    protected function setUp(): void
    {
        $this->registry = new TypeRegistry();
        $this->compact = new Compact($this->registry);
    }

    // ==================== Single Byte Mode (0-63) ====================

    public function testEncodeZero(): void
    {
        $result = $this->compact->encode(0);
        $this->assertEquals('0x00', $result->toHex());
    }

    public function testEncodeOne(): void
    {
        $result = $this->compact->encode(1);
        $this->assertEquals('0x04', $result->toHex());
    }

    public function testEncodeMaxSingleByte(): void
    {
        $result = $this->compact->encode(63);
        $this->assertEquals('0xfc', $result->toHex());
    }

    public function testDecodeZero(): void
    {
        $bytes = ScaleBytes::fromHex('0x00');
        $this->assertEquals(0, $this->compact->decode($bytes));
    }

    public function testDecodeMaxSingleByte(): void
    {
        $bytes = ScaleBytes::fromHex('0xfc');
        $this->assertEquals(63, $this->compact->decode($bytes));
    }

    // ==================== Two Byte Mode (64-16383) ====================

    public function testEncodeMinTwoByte(): void
    {
        $result = $this->compact->encode(64);
        $this->assertEquals('0x0101', $result->toHex());
    }

    public function testEncodeMaxTwoByte(): void
    {
        $result = $this->compact->encode(16383);
        $this->assertEquals(2, count($result->toBytes()));
    }

    public function testDecodeMinTwoByte(): void
    {
        $bytes = ScaleBytes::fromHex('0x0101');
        $this->assertEquals(64, $this->compact->decode($bytes));
    }

    // ==================== Four Byte Mode (16384-1073741823) ====================

    public function testEncodeMinFourByte(): void
    {
        $result = $this->compact->encode(16384);
        $this->assertEquals(4, count($result->toBytes()));
    }

    public function testEncodeMaxFourByte(): void
    {
        $result = $this->compact->encode(1073741823);
        $this->assertEquals(4, count($result->toBytes()));
    }

    public function testRoundTripFourByte(): void
    {
        $values = [16384, 1000000, 100000000, 1073741823];
        foreach ($values as $value) {
            $encoded = $this->compact->encode($value);
            $decoded = $this->compact->decode(ScaleBytes::fromBytes($encoded->toBytes()));
            $this->assertEquals($value, $decoded);
        }
    }

    // ==================== Big Integer Mode (> 1073741823) ====================

    public function testEncodeMinBigInt(): void
    {
        $result = $this->compact->encode(1073741824);
        $firstByte = $result->toBytes()[0];
        $this->assertEquals(0x03, $firstByte & 0x03);
    }

    public function testEncodeLargeValue(): void
    {
        $value = '1000000000000000000';
        $result = $this->compact->encode($value);
        $decoded = $this->compact->decode(ScaleBytes::fromBytes($result->toBytes()));
        $this->assertEquals($value, (string) $decoded);
    }

    public function testEncodeU128Max(): void
    {
        $value = '340282366920938463463374607431768211455';
        $result = $this->compact->encode($value);
        $decoded = $this->compact->decode(ScaleBytes::fromBytes($result->toBytes()));
        $this->assertEquals($value, (string) $decoded);
    }

    // ==================== String Input Tests ====================

    public function testEncodeStringInput(): void
    {
        $result = $this->compact->encode('1000');
        $decoded = $this->compact->decode(ScaleBytes::fromBytes($result->toBytes()));
        $this->assertEquals(1000, $decoded);
    }

    public function testEncodeLargeStringInput(): void
    {
        $value = '999999999999999999999999';
        $result = $this->compact->encode($value);
        $decoded = $this->compact->decode(ScaleBytes::fromBytes($result->toBytes()));
        $this->assertEquals($value, (string) $decoded);
    }

    // ==================== Invalid Input Tests ====================

    public function testEncodeNegativeThrowsException(): void
    {
        $this->expectException(ScaleEncodeException::class);
        $this->compact->encode(-1);
    }

    public function testEncodeArrayThrowsException(): void
    {
        $this->expectException(ScaleEncodeException::class);
        $this->compact->encode([]);
    }

    // ==================== Validation Tests ====================

    public function testIsValidForPositiveIntegers(): void
    {
        $this->assertTrue($this->compact->isValid(0));
        $this->assertTrue($this->compact->isValid(100));
        $this->assertTrue($this->compact->isValid(1000000));
        $this->assertTrue($this->compact->isValid('123456789'));
    }

    public function testIsValidRejectsNegative(): void
    {
        $this->assertFalse($this->compact->isValid(-1));
        $this->assertFalse($this->compact->isValid('-100'));
    }

    public function testIsValidRejectsInvalidTypes(): void
    {
        $this->assertFalse($this->compact->isValid([]));
        $this->assertFalse($this->compact->isValid(null));
    }

    // ==================== Round Trip Tests ====================

    public function testRoundTripAllModes(): void
    {
        $values = [
            0, 1, 63,           // Single byte
            64, 1000, 16383,    // Two byte
            16384, 1000000, 1073741823, // Four byte
            1073741824, 10000000000, // Big integer
        ];

        foreach ($values as $value) {
            $encoded = $this->compact->encode($value);
            $decoded = $this->compact->decode(ScaleBytes::fromBytes($encoded->toBytes()));
            $this->assertEquals($value, $decoded, "Round trip failed for value $value");
        }
    }
}
