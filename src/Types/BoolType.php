<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * Boolean type implementation.
 * 
 * Encodes/decodes boolean values in SCALE format.
 * - false is encoded as 0x00
 * - true is encoded as 0x01
 */
class BoolType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        if (!is_bool($value)) {
            throw ScaleEncodeException::invalidType('bool', $value);
        }

        return ScaleBytes::fromBytes([$value ? 1 : 0]);
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): mixed
    {
        $byte = $bytes->readByte();

        return match ($byte) {
            0 => false,
            1 => true,
            default => throw ScaleDecodeException::invalidBoolValue($byte)
        };
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        return is_bool($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'Bool';
    }
}
