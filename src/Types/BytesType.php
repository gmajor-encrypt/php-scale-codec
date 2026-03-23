<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;

/**
 * Bytes type implementation.
 * 
 * A variable-length byte array.
 * Encoded as: Compact(length) + raw_bytes
 */
class BytesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        // Accept string (hex or raw) or array
        if (is_string($value)) {
            // Check if hex string
            if (str_starts_with($value, '0x')) {
                $bytes = ScaleBytes::fromHex($value);
            } else {
                // Raw string - convert to bytes
                $bytes = ScaleBytes::fromBytes(array_map('ord', str_split($value)));
            }
        } elseif (is_array($value)) {
            $bytes = ScaleBytes::fromBytes($value);
        } else {
            throw ScaleEncodeException::invalidType('Bytes', $value);
        }

        // Prepend length as Compact
        $length = count($bytes->toBytes());
        $compact = new Compact($this->registry);
        $lengthBytes = $compact->encode($length);

        return $lengthBytes->concat($bytes);
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
        $rawBytes = $bytes->readBytes($length);

        // Return as hex string
        return '0x' . bin2hex(implode('', array_map('chr', $rawBytes)));
    }

    /**
     * Decode bytes as raw array.
     *
     * @return array<int> Array of bytes
     */
    public function decodeToArray(ScaleBytes $bytes): array
    {
        $compact = new Compact($this->registry);
        $length = $compact->decode($bytes);

        return $bytes->readBytes($length);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $byte) {
                if (!is_int($byte) || $byte < 0 || $byte > 255) {
                    return false;
                }
            }
            return true;
        }

        if (is_string($value)) {
            if (str_starts_with($value, '0x')) {
                return ctype_xdigit(substr($value, 2));
            }
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'Bytes';
    }
}