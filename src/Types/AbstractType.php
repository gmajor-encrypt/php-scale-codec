<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * Abstract base class for SCALE types.
 * 
 * Provides common functionality for all type implementations.
 */
abstract class AbstractType implements TypeInterface
{
    /**
     * @var string The type string representation
     */
    protected string $typeString = '';

    /**
     * @var TypeInterface|null Inner type for parameterized types
     */
    protected ?TypeInterface $innerType = null;

    /**
     * @var TypeRegistry The type registry
     */
    protected TypeRegistry $registry;

    /**
     * Create a new type instance.
     *
     * @param TypeRegistry $registry The type registry
     */
    public function __construct(TypeRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        $parts = explode('\\', static::class);
        return end($parts);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeString(): string
    {
        return $this->typeString;
    }

    /**
     * {@inheritdoc}
     */
    public function setTypeString(string $typeString): void
    {
        $this->typeString = $typeString;
    }

    /**
     * {@inheritdoc}
     */
    public function getInnerType(): ?TypeInterface
    {
        return $this->innerType;
    }

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
    public function requiresMetadata(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function canEncode(mixed $value): bool
    {
        return $this->isValid($value);
    }

    /**
     * {@inheritdoc}
     */
    public function peek(ScaleBytes $bytes): mixed
    {
        $currentOffset = $bytes->getOffset();
        $value = $this->decode($bytes);
        
        // Reset offset to peek
        // Note: ScaleBytes is immutable for data, so we need to create a new approach
        // For now, we'll just return the decoded value
        return $value;
    }

    /**
     * Read bytes in little-endian order and convert to integer.
     *
     * @param ScaleBytes $bytes The byte source
     * @param int $length Number of bytes
     * @return int The integer value
     */
    protected function readLittleEndianInt(ScaleBytes $bytes, int $length): int
    {
        $data = $bytes->readBytes($length);
        $value = 0;
        for ($i = 0; $i < $length; $i++) {
            $value |= $data[$i] << ($i * 8);
        }
        return $value;
    }

    /**
     * Write an integer in little-endian order.
     *
     * @param int $value The integer value
     * @param int $length Number of bytes
     * @return ScaleBytes The encoded bytes
     */
    protected function writeLittleEndianInt(int $value, int $length): ScaleBytes
    {
        $bytes = [];
        for ($i = 0; $i < $length; $i++) {
            $bytes[] = ($value >> ($i * 8)) & 0xFF;
        }
        return ScaleBytes::fromBytes($bytes);
    }

    /**
     * Encode a length prefix (compact encoding).
     *
     * @param int $length The length value
     * @return ScaleBytes The encoded length
     */
    protected function encodeLength(int $length): ScaleBytes
    {
        if ($length < 0) {
            throw new ScaleEncodeException('Length cannot be negative');
        }

        if ($length <= 0x3F) {
            // 0b00: single byte
            return ScaleBytes::fromBytes([$length << 2]);
        } elseif ($length <= 0x3FFF) {
            // 0b01: two bytes
            return ScaleBytes::fromBytes([
                ($length << 2) | 0x01,
                ($length >> 6) & 0xFF
            ]);
        } elseif ($length <= 0x3FFFFFFF) {
            // 0b10: four bytes
            return ScaleBytes::fromBytes([
                ($length << 2) | 0x02,
                ($length >> 6) & 0xFF,
                ($length >> 14) & 0xFF,
                ($length >> 22) & 0xFF
            ]);
        } else {
            // 0b11: big integer
            $bytes = [];
            $remaining = $length;
            while ($remaining > 0) {
                $bytes[] = $remaining & 0xFF;
                $remaining >>= 8;
            }
            array_unshift($bytes, (count($bytes) - 4) << 2 | 0x03);
            return ScaleBytes::fromBytes($bytes);
        }
    }

    /**
     * Decode a length prefix (compact encoding).
     *
     * @param ScaleBytes $bytes The byte source
     * @return int The decoded length
     */
    protected function decodeLength(ScaleBytes $bytes): int
    {
        $firstByte = $bytes->readByte();
        $mode = $firstByte & 0x03;

        return match ($mode) {
            0 => $firstByte >> 2, // single byte
            1 => ($firstByte >> 2) | ($bytes->readByte() << 6), // two bytes
            2 => ($firstByte >> 2) 
                | ($bytes->readByte() << 6)
                | ($bytes->readByte() << 14)
                | ($bytes->readByte() << 22), // four bytes
            3 => $this->decodeBigLength($bytes, $firstByte >> 2), // big integer
            default => throw new ScaleDecodeException('Invalid compact encoding mode')
        };
    }

    /**
     * Decode a big length (compact encoding with 0b11 prefix).
     *
     * @param ScaleBytes $bytes The byte source
     * @param int $lengthBytes Number of additional bytes
     * @return int The decoded length
     */
    private function decodeBigLength(ScaleBytes $bytes, int $lengthBytes): int
    {
        $length = 0;
        for ($i = 0; $i <= $lengthBytes; $i++) {
            $length |= $bytes->readByte() << ($i * 8);
        }
        return $length;
    }
}
