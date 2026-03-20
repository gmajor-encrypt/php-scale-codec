<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;

/**
 * Null/Empty type implementation.
 * 
 * Represents an empty value that encodes to zero bytes.
 */
class NullType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        return ScaleBytes::empty();
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): mixed
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        return $value === null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'Null';
    }
}
