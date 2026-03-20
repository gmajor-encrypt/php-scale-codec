<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when an invalid type is encountered.
 */
class InvalidTypeException extends InvalidArgumentException
{
    /**
     * Create an exception for type mismatch.
     *
     * @param string $expected The expected type
     * @param string $actual The actual type
     * @return self
     */
    public static function mismatch(string $expected, string $actual): self
    {
        return new self(
            sprintf('Type mismatch: expected "%s" but got "%s"', $expected, $actual)
        );
    }

    /**
     * Create an exception for unregistered type.
     *
     * @param string $type The unregistered type
     * @return self
     */
    public static function notRegistered(string $type): self
    {
        return new self(sprintf('Type "%s" is not registered', $type));
    }

    /**
     * Create an exception for invalid type string format.
     *
     * @param string $typeString The invalid type string
     * @param string $reason The reason for invalidity
     * @return self
     */
    public static function invalidFormat(string $typeString, string $reason): self
    {
        return new self(
            sprintf('Invalid type string format "%s": %s', $typeString, $reason)
        );
    }

    /**
     * Create an exception for circular type reference.
     *
     * @param string $type The type with circular reference
     * @return self
     */
    public static function circularReference(string $type): self
    {
        return new self(sprintf('Circular type reference detected for "%s"', $type));
    }
}
