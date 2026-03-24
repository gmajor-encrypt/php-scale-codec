<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Metadata;

/**
 * Represents a type definition in Substrate metadata.
 */
class TypeDefinition
{
    /**
     * @param int $id Type ID (for portable types)
     * @param string $path Type path (namespace)
     * @param array $params Type parameters
     * @param array $def Type definition (struct/enum/sequence/etc)
     * @param array $docs Documentation
     */
    public function __construct(
        public readonly int $id,
        public readonly string $path = '',
        public readonly array $params = [],
        public readonly array $def = [],
        public readonly array $docs = [],
    ) {}

    /**
     * Get the type kind (struct, enum, sequence, etc).
     */
    public function getKind(): string
    {
        return array_key_first($this->def) ?? 'unknown';
    }

    /**
     * Check if this is a composite type (struct).
     */
    public function isComposite(): bool
    {
        return $this->getKind() === 'composite';
    }

    /**
     * Check if this is a variant type (enum).
     */
    public function isVariant(): bool
    {
        return $this->getKind() === 'variant';
    }

    /**
     * Check if this is a sequence type (Vec).
     */
    public function isSequence(): bool
    {
        return $this->getKind() === 'sequence';
    }

    /**
     * Check if this is an array type.
     */
    public function isArray(): bool
    {
        return $this->getKind() === 'array';
    }

    /**
     * Check if this is a tuple type.
     */
    public function isTuple(): bool
    {
        return $this->getKind() === 'tuple';
    }

    /**
     * Check if this is a primitive type.
     */
    public function isPrimitive(): bool
    {
        return $this->getKind() === 'primitive';
    }

    /**
     * Check if this is a compact type.
     */
    public function isCompact(): bool
    {
        return $this->getKind() === 'compact';
    }

    /**
     * Check if this is a bit sequence type.
     */
    public function isBitSequence(): bool
    {
        return $this->getKind() === 'bitsequence';
    }

    /**
     * Get composite fields.
     */
    public function getFields(): array
    {
        return $this->def['composite']['fields'] ?? [];
    }

    /**
     * Get enum variants.
     */
    public function getVariants(): array
    {
        return $this->def['variant']['variants'] ?? [];
    }

    /**
     * Get sequence element type.
     */
    public function getElementType(): ?int
    {
        return $this->def['sequence']['type'] ?? null;
    }

    /**
     * Get array element type and length.
     */
    public function getArrayInfo(): array
    {
        return [
            'type' => $this->def['array']['type'] ?? null,
            'len' => $this->def['array']['len'] ?? 0,
        ];
    }

    /**
     * Get tuple element types.
     */
    public function getTupleTypes(): array
    {
        return $this->def['tuple'] ?? [];
    }

    /**
     * Get primitive type.
     */
    public function getPrimitiveType(): ?string
    {
        return $this->def['primitive'] ?? null;
    }
}
