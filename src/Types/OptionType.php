<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * Option<T> optional type implementation.
 * 
 * Represents an optional value that may or may not exist.
 * Encoded as:
 * - None: 0x00
 * - Some(value): 0x01 + encoded_value
 */
class OptionType extends AbstractType
{
    /**
     * @var TypeInterface|null The inner type
     */
    protected ?TypeInterface $innerType = null;

    /**
     * {@inheritdoc}
     */
    public function setInnerType(TypeInterface $type): void
    {
        $this->innerType = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        if ($this->innerType === null) {
            throw new ScaleEncodeException('Option inner type not set');
        }

        if ($value === null) {
            // None case
            return ScaleBytes::fromBytes([0x00]);
        }

        // Some case
        return ScaleBytes::fromBytes([0x01])->concat($this->innerType->encode($value));
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): mixed
    {
        if ($this->innerType === null) {
            throw new ScaleDecodeException('Option inner type not set');
        }

        $flag = $bytes->readByte();

        if ($flag === 0x00) {
            return null;
        }

        if ($flag === 0x01) {
            return $this->innerType->decode($bytes);
        }

        throw new ScaleDecodeException(sprintf('Invalid Option flag: %d', $flag));
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if ($this->innerType === null) {
            return true;
        }

        return $this->innerType->isValid($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'Option';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeString(): string
    {
        if ($this->innerType !== null) {
            return 'Option<' . $this->innerType->getTypeString() . '>';
        }
        return 'Option';
    }
}
