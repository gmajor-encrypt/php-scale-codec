<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * Fixed-size array type implementation.
 * 
 * A fixed-size array of elements of type T with length N.
 * Encoded as: element1 + element2 + ... + elementN (no length prefix)
 */
class FixedArrayType extends AbstractType
{
    /**
     * @var TypeInterface|null The element type
     */
    protected ?TypeInterface $elementType = null;

    /**
     * @var int The fixed length
     */
    protected int $length = 0;

    /**
     * Set the element type.
     */
    public function setElementType(TypeInterface $type): void
    {
        $this->elementType = $type;
    }

    /**
     * Set the fixed length.
     */
    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        if (!is_array($value)) {
            throw ScaleEncodeException::invalidType('FixedArray', $value);
        }

        if ($this->elementType === null) {
            throw new ScaleEncodeException('FixedArray element type not set');
        }

        if (count($value) !== $this->length) {
            throw new ScaleEncodeException(sprintf(
                'FixedArray length mismatch: expected %d, got %d',
                $this->length,
                count($value)
            ));
        }

        $result = ScaleBytes::empty();
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
            throw new ScaleDecodeException('FixedArray element type not set');
        }

        $result = [];
        for ($i = 0; $i < $this->length; $i++) {
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

        if (count($value) !== $this->length) {
            return false;
        }

        if ($this->elementType === null) {
            return true;
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
        return 'FixedArray';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeString(): string
    {
        if ($this->elementType !== null && $this->length > 0) {
            return sprintf('[%s; %d]', $this->elementType->getTypeString(), $this->length);
        }
        return 'FixedArray';
    }
}
