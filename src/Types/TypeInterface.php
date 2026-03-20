<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Encoder\EncoderInterface;
use Substrate\ScaleCodec\Decoder\DecoderInterface;

/**
 * Interface for SCALE types.
 * 
 * A SCALE type can both encode and decode values.
 */
interface TypeInterface extends EncoderInterface, DecoderInterface
{
    /**
     * Get the type name.
     *
     * @return string The type name
     */
    public function getTypeName(): string;

    /**
     * Get the type string representation.
     *
     * @return string The type string (e.g., "Vec<U8>", "Option<Bool>")
     */
    public function getTypeString(): string;

    /**
     * Set the type string representation.
     *
     * @param string $typeString The type string
     */
    public function setTypeString(string $typeString): void;

    /**
     * Get the inner type for parameterized types (e.g., T in Vec<T>).
     *
     * @return TypeInterface|null The inner type or null if not parameterized
     */
    public function getInnerType(): ?TypeInterface;

    /**
     * Set the inner type for parameterized types.
     *
     * @param TypeInterface $type The inner type
     */
    public function setInnerType(TypeInterface $type): void;

    /**
     * Check if this type requires metadata for decoding.
     *
     * @return bool True if metadata is required
     */
    public function requiresMetadata(): bool;

    /**
     * Validate a value against this type.
     *
     * @param mixed $value The value to validate
     * @return bool True if the value is valid for this type
     */
    public function isValid(mixed $value): bool;
}
