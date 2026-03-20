<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Exception;

use RuntimeException;

/**
 * Exception thrown when SCALE encoding fails.
 */
class ScaleEncodeException extends RuntimeException
{
    /**
     * Create an exception for invalid type during encoding.
     *
     * @param string $expectedType The expected type
     * @param mixed $actualValue The actual value
     * @return self
     */
    public static function invalidType(string $expectedType, mixed $actualValue): self
    {
        $actualType = is_object($actualValue) ? get_class($actualValue) : gettype($actualValue);
        return new self(
            sprintf('Expected type "%s" but got "%s"', $expectedType, $actualType)
        );
    }

    /**
     * Create an exception for value out of range.
     *
     * @param string $type The type
     * @param mixed $value The out-of-range value
     * @param string $range The valid range description
     * @return self
     */
    public static function outOfRange(string $type, mixed $value, string $range): self
    {
        return new self(
            sprintf('Value %s is out of range for type "%s". Valid range: %s', 
                is_scalar($value) ? (string)$value : gettype($value),
                $type,
                $range
            )
        );
    }

    /**
     * Create an exception for unsupported type.
     *
     * @param string $type The unsupported type
     * @return self
     */
    public static function unsupportedType(string $type): self
    {
        return new self(sprintf('Unsupported type for encoding: "%s"', $type));
    }
}
