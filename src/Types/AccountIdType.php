<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * AccountId/Address type implementation.
 * 
 * Represents a substrate account address.
 * Supports multiple formats:
 * - 32-byte AccountId (ed25519, sr25519)
 * - 20-byte AccountId (Ethereum-compatible)
 */
class AccountIdType extends AbstractType
{
    /**
     * @var int Expected length in bytes (default 32 for Substrate)
     */
    protected int $expectedLength = 32;

    /**
     * Set expected length for different address formats.
     *
     * @param int $length Expected byte length
     */
    public function setExpectedLength(int $length): void
    {
        $this->expectedLength = $length;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        if (is_string($value)) {
            // Handle hex string
            if (str_starts_with($value, '0x')) {
                $hex = substr($value, 2);
                $bytes = array_map('hexdec', str_split($hex, 2));
            } else {
                // Assume raw bytes
                $bytes = array_values(unpack('C*', $value));
            }
        } elseif (is_array($value)) {
            $bytes = $value;
        } else {
            throw ScaleEncodeException::invalidType('AccountId', $value);
        }

        if (count($bytes) !== $this->expectedLength) {
            throw new ScaleEncodeException(sprintf(
                'AccountId length mismatch: expected %d bytes, got %d',
                $this->expectedLength,
                count($bytes)
            ));
        }

        return ScaleBytes::fromBytes($bytes);
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): string
    {
        $rawBytes = $bytes->readBytes($this->expectedLength);

        // Return as hex string
        return '0x' . bin2hex(pack('C*', ...$rawBytes));
    }

    /**
     * Decode as raw byte array.
     *
     * @return array<int>
     */
    public function decodeToArray(ScaleBytes $bytes): array
    {
        return $bytes->readBytes($this->expectedLength);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if (is_string($value)) {
            if (str_starts_with($value, '0x')) {
                $hex = substr($value, 2);
                if (!ctype_xdigit($hex)) {
                    return false;
                }
                return strlen($hex) / 2 === $this->expectedLength;
            }
            return strlen($value) === $this->expectedLength;
        }

        if (is_array($value)) {
            return count($value) === $this->expectedLength;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'AccountId';
    }
}