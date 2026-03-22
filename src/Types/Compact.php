<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;
use GMP;

/**
 * Compact type implementation for SCALE codec.
 * 
 * Compact encoding is used for length prefixes and compact integers.
 * 
 * Encoding rules:
 * - 0-63: single byte mode, value encoded as (value << 2) | 0b00
 * - 64-16383: two byte mode, value encoded as (value << 2) | 0b01
 * - 16384-1073741823: four byte mode, value encoded as (value << 2) | 0b10
 * - Larger: big integer mode, (byteLength - 4) << 2 | 0b11 followed by bytes
 */
class Compact extends AbstractType
{
    /**
     * @var int|string|null Maximum value for validation
     */
    protected int|string|null $maxValue = null;

    /**
     * @var string|null Inner type for parameterized compacts like Compact<Balance>
     */
    protected ?string $innerTypeName = null;

    /**
     * Set the inner type name for parameterized compacts.
     */
    public function setInnerTypeName(string $typeName): void
    {
        $this->innerTypeName = $typeName;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        // Handle string/GMP for large values
        if (is_string($value)) {
            $value = gmp_init($value);
        }

        if ($value instanceof GMP) {
            return $this->encodeGmp($value);
        }

        if (!is_int($value)) {
            throw ScaleEncodeException::invalidType('Compact', $value);
        }

        if ($value < 0) {
            throw ScaleEncodeException::outOfRange('Compact', $value, '>= 0');
        }

        return $this->encodeInt($value);
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): int|string
    {
        $firstByte = $bytes->readByte();
        $mode = $firstByte & 0x03;

        return match ($mode) {
            0 => $firstByte >> 2, // Single byte mode
            1 => $this->decodeTwoByte($firstByte, $bytes), // Two byte mode
            2 => $this->decodeFourByte($firstByte, $bytes), // Four byte mode
            3 => $this->decodeBigInt($firstByte, $bytes), // Big integer mode
            default => throw new ScaleDecodeException('Invalid compact mode'),
        };
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if (is_int($value)) {
            return $value >= 0;
        }

        if (is_string($value) && ctype_digit($value)) {
            return true;
        }

        if ($value instanceof GMP) {
            return gmp_cmp($value, 0) >= 0;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'Compact';
    }

    /**
     * Encode an integer value using compact encoding.
     */
    protected function encodeInt(int $value): ScaleBytes
    {
        if ($value <= 0x3F) {
            // Single byte mode: 0b00
            return ScaleBytes::fromBytes([$value << 2]);
        }

        if ($value <= 0x3FFF) {
            // Two byte mode: 0b01
            return ScaleBytes::fromBytes([
                ($value << 2) | 0x01,
                ($value >> 6) & 0xFF,
            ]);
        }

        if ($value <= 0x3FFFFFFF) {
            // Four byte mode: 0b10
            return ScaleBytes::fromBytes([
                ($value << 2) | 0x02,
                ($value >> 6) & 0xFF,
                ($value >> 14) & 0xFF,
                ($value >> 22) & 0xFF,
            ]);
        }

        // For larger values, convert to GMP and use big integer mode
        return $this->encodeGmp(gmp_init($value));
    }

    /**
     * Encode a GMP value using compact encoding (big integer mode).
     */
    protected function encodeGmp(GMP $value): ScaleBytes
    {
        // Convert to little-endian bytes
        $hex = gmp_strval($value, 16);
        if (strlen($hex) % 2 !== 0) {
            $hex = '0' . $hex;
        }
        
        $bytes = [];
        for ($i = strlen($hex) - 2; $i >= 0; $i -= 2) {
            $bytes[] = hexdec(substr($hex, $i, 2));
        }
        
        $byteLength = count($bytes);
        
        // Big integer mode: 0b11
        // First byte: (byteLength - 4) << 2 | 0b11
        $prefix = (($byteLength - 4) << 2) | 0x03;
        
        return ScaleBytes::fromBytes(array_merge([$prefix], $bytes));
    }

    /**
     * Decode two byte mode compact.
     */
    protected function decodeTwoByte(int $firstByte, ScaleBytes $bytes): int
    {
        $secondByte = $bytes->readByte();
        return ($firstByte >> 2) | ($secondByte << 6);
    }

    /**
     * Decode four byte mode compact.
     */
    protected function decodeFourByte(int $firstByte, ScaleBytes $bytes): int
    {
        $b2 = $bytes->readByte();
        $b3 = $bytes->readByte();
        $b4 = $bytes->readByte();
        
        return ($firstByte >> 2) | ($b2 << 6) | ($b3 << 14) | ($b4 << 22);
    }

    /**
     * Decode big integer mode compact.
     * Returns string for values that exceed PHP_INT_MAX.
     */
    protected function decodeBigInt(int $firstByte, ScaleBytes $bytes): int|string
    {
        // Number of bytes = (firstByte >> 2) + 4
        $byteLength = ($firstByte >> 2) + 4;
        
        $rawBytes = $bytes->readBytes($byteLength);
        
        // Convert little-endian bytes to GMP
        $value = gmp_init(0);
        for ($i = 0; $i < $byteLength; $i++) {
            $value = gmp_add($value, gmp_mul(gmp_init($rawBytes[$i]), gmp_pow(256, $i)));
        }
        
        // Return as int if within range, otherwise as string
        if (gmp_cmp($value, PHP_INT_MAX) <= 0 && gmp_cmp($value, PHP_INT_MIN) >= 0) {
            return (int) gmp_intval($value);
        }
        
        return gmp_strval($value);
    }

    /**
     * Set the maximum value for validation.
     */
    public function setMaxValue(int|string $maxValue): void
    {
        $this->maxValue = $maxValue;
    }
}