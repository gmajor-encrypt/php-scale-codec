<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Exception;

use RuntimeException;

/**
 * Exception thrown when SCALE decoding fails.
 */
class ScaleDecodeException extends RuntimeException
{
    /**
     * Create an exception for insufficient bytes.
     *
     * @param int $required The number of bytes required
     * @param int $available The number of bytes available
     * @return self
     */
    public static function insufficientBytes(int $required, int $available): self
    {
        return new self(
            sprintf('Insufficient bytes: required %d, available %d', $required, $available)
        );
    }

    /**
     * Create an exception for invalid enum variant.
     *
     * @param int $variantIndex The invalid variant index
     * @param array $validVariants The list of valid variant names
     * @return self
     */
    public static function invalidEnumVariant(int $variantIndex, array $validVariants): self
    {
        return new self(
            sprintf('Invalid enum variant index %d. Valid variants: %s', 
                $variantIndex,
                implode(', ', $validVariants)
            )
        );
    }

    /**
     * Create an exception for invalid bool value.
     *
     * @param int $value The invalid value
     * @return self
     */
    public static function invalidBoolValue(int $value): self
    {
        return new self(
            sprintf('Invalid bool value: %d. Expected 0 or 1.', $value)
        );
    }

    /**
     * Create an exception for malformed data.
     *
     * @param string $message The error message
     * @return self
     */
    public static function malformedData(string $message): self
    {
        return new self('Malformed data: ' . $message);
    }

    /**
     * Create an exception for unknown type.
     *
     * @param string $type The unknown type
     * @return self
     */
    public static function unknownType(string $type): self
    {
        return new self(sprintf('Unknown type: "%s"', $type));
    }
}
