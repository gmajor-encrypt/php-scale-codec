<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Bytes;

use InvalidArgumentException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * Represents a sequence of bytes for SCALE encoding/decoding.
 * 
 * Provides efficient byte manipulation with read offset tracking.
 */
class ScaleBytes implements \Stringable
{
    /**
     * @var array<int> The byte data
     */
    private readonly array $data;

    /**
     * @var int Current read offset
     */
    private int $offset = 0;

    /**
     * Create a new ScaleBytes instance.
     *
     * @param string|array<int> $data Hex string or byte array
     * @throws InvalidArgumentException If data is not valid
     */
    public function __construct(string|array $data)
    {
        if (is_string($data)) {
            $this->data = self::hexToBytes($data);
        } elseif (is_array($data)) {
            $this->data = array_values($data);
        } else {
            throw new InvalidArgumentException(
                sprintf('Expected hex string or byte array, got %s', gettype($data))
            );
        }
    }

    /**
     * Create from a hex string.
     *
     * @param string $hex The hex string (with or without 0x prefix)
     * @return self
     */
    public static function fromHex(string $hex): self
    {
        return new self($hex);
    }

    /**
     * Create from a byte array.
     *
     * @param array<int> $bytes The byte array
     * @return self
     */
    public static function fromBytes(array $bytes): self
    {
        return new self($bytes);
    }

    /**
     * Create an empty instance.
     *
     * @return self
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Read the next N bytes and advance the offset.
     *
     * @param int $length Number of bytes to read
     * @return array<int> The read bytes
     * @throws ScaleDecodeException If not enough bytes available
     */
    public function readBytes(int $length): array
    {
        if ($length < 0) {
            throw new InvalidArgumentException('Length must be non-negative');
        }

        if ($this->remaining() < $length) {
            throw ScaleDecodeException::insufficientBytes($length, $this->remaining());
        }

        $bytes = array_slice($this->data, $this->offset, $length);
        $this->offset += $length;

        return $bytes;
    }

    /**
     * Peek at the next N bytes without advancing the offset.
     *
     * @param int $length Number of bytes to peek
     * @return array<int> The peeked bytes
     * @throws ScaleDecodeException If not enough bytes available
     */
    public function peekBytes(int $length): array
    {
        if ($this->remaining() < $length) {
            throw ScaleDecodeException::insufficientBytes($length, $this->remaining());
        }

        return array_slice($this->data, $this->offset, $length);
    }

    /**
     * Read a single byte and advance the offset.
     *
     * @return int The byte value (0-255)
     */
    public function readByte(): int
    {
        return $this->readBytes(1)[0];
    }

    /**
     * Peek at a single byte without advancing the offset.
     *
     * @return int The byte value (0-255)
     */
    public function peekByte(): int
    {
        return $this->peekBytes(1)[0];
    }

    /**
     * Get the remaining number of bytes.
     *
     * @return int Number of remaining bytes
     */
    public function remaining(): int
    {
        return count($this->data) - $this->offset;
    }

    /**
     * Get the total number of bytes.
     *
     * @return int Total byte count
     */
    public function length(): int
    {
        return count($this->data);
    }

    /**
     * Get the current read offset.
     *
     * @return int The offset
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Check if there are more bytes to read.
     *
     * @return bool True if there are remaining bytes
     */
    public function hasRemaining(): bool
    {
        return $this->remaining() > 0;
    }

    /**
     * Check if all bytes have been consumed.
     *
     * @return bool True if offset is at the end
     */
    public function isExhausted(): bool
    {
        return !$this->hasRemaining();
    }

    /**
     * Reset the read offset to the beginning.
     */
    public function reset(): void
    {
        $this->offset = 0;
    }

    /**
     * Get all bytes as an array.
     *
     * @return array<int> All bytes
     */
    public function toBytes(): array
    {
        return $this->data;
    }

    /**
     * Get all bytes as a hex string.
     *
     * @param bool $withPrefix Whether to include 0x prefix
     * @return string The hex string
     */
    public function toHex(bool $withPrefix = true): string
    {
        $hex = bin2hex(implode('', array_map('chr', $this->data)));
        return $withPrefix ? '0x' . $hex : $hex;
    }

    /**
     * Concatenate with another ScaleBytes instance.
     *
     * @param ScaleBytes $other The other instance
     * @return ScaleBytes New instance with concatenated bytes
     */
    public function concat(ScaleBytes $other): ScaleBytes
    {
        return new self(array_merge($this->data, $other->data));
    }

    /**
     * Create a slice of bytes.
     *
     * @param int $start Start position
     * @param int|null $length Length (null for all remaining)
     * @return ScaleBytes New instance with sliced bytes
     */
    public function slice(int $start, ?int $length = null): ScaleBytes
    {
        $length ??= count($this->data) - $start;
        return new self(array_slice($this->data, $start, $length));
    }

    /**
     * String representation (hex).
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toHex();
    }

    /**
     * Convert a hex string to a byte array.
     *
     * @param string $hex The hex string
     * @return array<int> The byte array
     */
    private static function hexToBytes(string $hex): array
    {
        // Remove 0x prefix if present
        $hex = self::trimHex($hex);

        if (!ctype_xdigit($hex)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid hex string', $hex));
        }

        // Pad with leading zero if odd length
        if (strlen($hex) % 2 !== 0) {
            $hex = '0' . $hex;
        }

        $bytes = [];
        for ($i = 0; $i < strlen($hex); $i += 2) {
            $bytes[] = hexdec(substr($hex, $i, 2));
        }

        return $bytes;
    }

    /**
     * Remove 0x prefix from a hex string.
     *
     * @param string $hex The hex string
     * @return string The hex string without prefix
     */
    private static function trimHex(string $hex): string
    {
        if (str_starts_with(strtolower($hex), '0x')) {
            return substr($hex, 2);
        }
        return $hex;
    }
}
