<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * Tuple type implementation.
 * 
 * A fixed-size collection of values with different types.
 * Encoded as: element1 + element2 + ... (concatenated)
 */
class TupleType extends AbstractType
{
    /**
     * @var array<TypeInterface> Element types in order
     */
    protected array $elementTypes = [];

    /**
     * Set the element types.
     *
     * @param array<TypeInterface> $types
     */
    public function setElementTypes(array $types): void
    {
        $this->elementTypes = $types;
    }

    /**
     * Add an element type.
     */
    public function addElementType(TypeInterface $type): void
    {
        $this->elementTypes[] = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        if (!is_array($value)) {
            throw ScaleEncodeException::invalidType('Tuple', $value);
        }

        $expectedCount = count($this->elementTypes);
        $actualCount = count($value);

        if ($expectedCount !== $actualCount) {
            throw new ScaleEncodeException(
                sprintf('Tuple element count mismatch: expected %d, got %d', $expectedCount, $actualCount)
            );
        }

        $result = ScaleBytes::empty();
        $values = array_values($value);

        for ($i = 0; $i < $expectedCount; $i++) {
            $result = $result->concat($this->elementTypes[$i]->encode($values[$i]));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): array
    {
        $result = [];

        foreach ($this->elementTypes as $type) {
            $result[] = $type->decode($bytes);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $values = array_values($value);

        if (count($values) !== count($this->elementTypes)) {
            return false;
        }

        for ($i = 0; $i < count($this->elementTypes); $i++) {
            if (!$this->elementTypes[$i]->isValid($values[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'Tuple';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeString(): string
    {
        $typeStrings = array_map(
            fn(TypeInterface $type) => $type->getTypeString(),
            $this->elementTypes
        );
        return '(' . implode(', ', $typeStrings) . ')';
    }
}
