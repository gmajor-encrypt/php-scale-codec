<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * Vec<T> vector type implementation.
 * 
 * A dynamic-length array of elements of type T.
 * Encoded as: Compact(length) + elements
 */
class VecType extends AbstractType
{
    /**
     * @var TypeInterface|null The element type
     */
    protected ?TypeInterface $elementType = null;

    /**
     * Set the element type.
     */
    public function setElementType(TypeInterface $type): void
    {
        $this->elementType = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        if (!is_array($value)) {
            throw ScaleEncodeException::invalidType('Vec', $value);
        }

        if ($this->elementType === null) {
            throw new ScaleEncodeException('Vec element type not set');
        }

        // Encode length as Compact
        $length = count($value);
        $compact = new Compact($this->registry);
        $result = $compact->encode($length);

        // Encode each element
        foreach ($value as $element) {
            $result = $result->concat($this->elementType->encode($element));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): array
    {
        if ($this->elementType === null) {
            throw new ScaleDecodeException('Vec element type not set');
        }

        // Decode length from Compact
        $compact = new Compact($this->registry);
        $length = $compact->decode($bytes);

        // Decode each element
        $result = [];
        for ($i = 0; $i < $length; $i++) {
            $result[] = $this->elementType->decode($bytes);
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

        if ($this->elementType === null) {
            return true; // Can't validate without element type
        }

        foreach ($value as $element) {
            if (!$this->elementType->isValid($element)) {
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
        return 'Vec';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeString(): string
    {
        if ($this->elementType !== null) {
            return 'Vec<' . $this->elementType->getTypeString() . '>';
        }
        return 'Vec';
    }
}
