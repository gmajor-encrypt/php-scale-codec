<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;
use GMP;

/**
 * Abstract base class for unsigned integer types.
 */
abstract class AbstractUintType extends AbstractType
{
    /**
     * @var int Byte size for this integer type
     */
    protected int $byteSize;

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

        if ($value < 0) {
            throw ScaleEncodeException::outOfRange($this->getTypeName(), $value, '>= 0');
        }

        $maxInt = $this->getMaxInt();
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
        $value = $this->bytesToUint($rawBytes);

        // Return as string if it exceeds PHP_INT_MAX
        if ($this->byteSize > 8 && gmp_cmp($value, PHP_INT_MAX) > 0) {
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
            return $value >= 0 && $value <= $this->maxValue;
        }
        
        if (is_string($value) && ctype_digit($value)) {
            return gmp_cmp($value, (string) $this->maxValue) <= 0;
        }
        
        if ($value instanceof GMP) {
            return gmp_cmp($value, 0) >= 0 && gmp_cmp($value, $this->maxValue) <= 0;
        }
        
        return false;
    }

    /**
     * Encode an integer value.
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
     * Encode a GMP value.
     */
    protected function encodeGmp(GMP $value): ScaleBytes
    {
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
     * Convert bytes to unsigned integer.
     */
    protected function bytesToUint(array $bytes): GMP
    {
        $value = gmp_init(0);
        for ($i = 0; $i < count($bytes); $i++) {
            // Multiply current value by 256 and add byte
            $value = gmp_add($value, gmp_mul(gmp_init($bytes[$i]), gmp_pow(256, $i)));
        }
        return $value;
    }

    /**
     * Get maximum integer value for this type.
     */
    protected function getMaxInt(): int|string
    {
        return $this->maxValue;
    }
}
