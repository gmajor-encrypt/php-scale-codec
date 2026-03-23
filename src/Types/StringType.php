<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;

/**
 * String type implementation.
 * 
 * Encodes/decodes UTF-8 strings in SCALE format.
 * Encoded as: Compact(length) + utf8_bytes
 */
class StringType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        if (!is_string($value)) {
            throw ScaleEncodeException::invalidType('String', $value);
        }

        // Convert string to UTF-8 bytes
        $utf8Bytes = array_values(unpack('C*', $value));
        $length = count($utf8Bytes);

        // Encode length as Compact
        $compact = new Compact($this->registry);
        $result = $compact->encode($length);

        // Append string bytes
        $result = $result->concat(ScaleBytes::fromBytes($utf8Bytes));

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): string
    {
        // Decode length from Compact
        $compact = new Compact($this->registry);
        $length = $compact->decode($bytes);

        // Read bytes
        $utf8Bytes = $bytes->readBytes($length);

        // Convert bytes to string
        return pack('C*', ...$utf8Bytes);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Check if valid UTF-8
        return mb_check_encoding($value, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'String';
    }
}