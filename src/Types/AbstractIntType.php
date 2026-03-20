<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use GMP;

/**
 * Abstract base class for signed integer types.
 */
abstract class AbstractIntType extends AbstractType
{
    /**
     * @var int Byte size for this integer type
     */
    protected int $byteSize;

    /**
     * @var int|string Minimum value for this type
     */
    protected int|string $minValue;

    /**
     * @var int|string Maximum value for this type
     */
    protected int|string $maxValue;

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
            throw ScaleEncodeException::invalidType($this->getTypeName(), $value);
        }

        $minInt = $this->getMinInt();
        $maxInt = $this->getMaxInt();
        
        if (is_int($minInt) && $value < $minInt) {
            throw ScaleEncodeException::outOfRange($this->getTypeName(), $value, ">= {$minInt}");
        }
        
        if (is_int($maxInt) && $value > $maxInt) {
            throw ScaleEncodeException::outOfRange($this->getTypeName(), $value, "<= {$maxInt}");
        }

        return $this->encodeInt($value);
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): int|string
    {
        $rawBytes = $bytes->readBytes($this->byteSize);
        $value = $this->bytesToInt($rawBytes);

        // Return as string if it exceeds PHP_INT_MAX or is less than PHP_INT_MIN
        if ($this->byteSize > 8) {
            return gmp_strval($value);
        }

        return (int) gmp_intval($value);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if (is_int($value)) {
            return $value >= $this->minValue && $value <= $this->maxValue;
        }
        
        if (is_string($value) && (ctype_digit($value) || (str_starts_with($value, '-') && ctype_digit(substr($value, 1))))) {
            $cmpMin = gmp_cmp($value, (string) $this->minValue);
            $cmpMax = gmp_cmp($value, (string) $this->maxValue);
            return $cmpMin >= 0 && $cmpMax <= 0;
        }
        
        if ($value instanceof GMP) {
            $cmpMin = gmp_cmp($value, $this->minValue);
            $cmpMax = gmp_cmp($value, $this->maxValue);
            return $cmpMin >= 0 && $cmpMax <= 0;
        }
        
        return false;
    }

    /**
     * Encode an integer value using two's complement.
     */
    protected function encodeInt(int $value): ScaleBytes
    {
        $bytes = [];
        for ($i = 0; $i < $this->byteSize; $i++) {
            $bytes[] = ($value >> ($i * 8)) & 0xFF;
        }
        return ScaleBytes::fromBytes($bytes);
    }

    /**
     * Encode a GMP value using two's complement.
     */
    protected function encodeGmp(GMP $value): ScaleBytes
    {
        // If negative, convert to two's complement
        if (gmp_cmp($value, 0) < 0) {
            // Add 2^(bitWidth) to get two's complement representation
            $bitWidth = $this->byteSize * 8;
            $value = gmp_add($value, gmp_pow(2, $bitWidth));
        }
        
        $bytes = [];
        for ($i = 0; $i < $this->byteSize; $i++) {
            // Shift right by $i * 8 bits (divide by 256^i)
            $shifted = gmp_div_q($value, gmp_pow(256, $i));
            // Get lowest byte
            $bytes[] = gmp_intval(gmp_and($shifted, 0xFF));
        }
        return ScaleBytes::fromBytes($bytes);
    }

    /**
     * Convert bytes to signed integer using two's complement.
     */
    protected function bytesToInt(array $bytes): GMP
    {
        $value = gmp_init(0);
        for ($i = 0; $i < count($bytes); $i++) {
            // Multiply current value by 256 and add byte
            $value = gmp_add($value, gmp_mul(gmp_init($bytes[$i]), gmp_pow(256, $i)));
        }
        
        // Check if the sign bit is set (MSB of the last byte)
        $bitWidth = $this->byteSize * 8;
        $signBit = gmp_pow(2, $bitWidth - 1);
        
        if (gmp_cmp(gmp_and($value, $signBit), 0) !== 0) {
            // Negative number: convert from two's complement
            $value = gmp_sub($value, gmp_pow(2, $bitWidth));
        }
        
        return $value;
    }

    /**
     * Get minimum integer value for this type.
     */
    protected function getMinInt(): int|string
    {
        return $this->minValue;
    }

    /**
     * Get maximum integer value for this type.
     */
    protected function getMaxInt(): int|string
    {
        return $this->maxValue;
    }
}
