<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * MultiAddress type implementation.
 * 
 * Represents multiple address formats in SCALE.
 * 
 * Variants:
 * - Id(0): AccountId32 - 32 bytes
 * - Index(1): AccountIndex - compact encoded index
 * - Raw(2): Vec<U8> - raw bytes
 * - Address32(3): [u8; 32] - 32 bytes
 * - Address20(4): [u8; 20] - 20 bytes (Ethereum)
 */
class MultiAddressType extends AbstractType
{
    /**
     * @var array<int, string> Variant index to name mapping
     */
    protected array $indexToName = [
        0 => 'Id',
        1 => 'Index',
        2 => 'Raw',
        3 => 'Address32',
        4 => 'Address20',
    ];

    /**
     * @var array<string, int> Variant name to index mapping
     */
    protected array $nameToIndex = [
        'Id' => 0,
        'Index' => 1,
        'Raw' => 2,
        'Address32' => 3,
        'Address20' => 4,
    ];

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        if (!is_array($value) || count($value) !== 1) {
            throw ScaleEncodeException::invalidType('MultiAddress', $value);
        }

        $variant = array_key_first($value);
        $data = $value[$variant];

        if (!isset($this->nameToIndex[$variant])) {
            throw new ScaleEncodeException(sprintf('Unknown MultiAddress variant: %s', $variant));
        }

        $index = $this->nameToIndex[$variant];
        $result = ScaleBytes::fromBytes([$index]);

        switch ($variant) {
            case 'Id':
            case 'Address32':
                // 32-byte address
                $result = $result->concat($this->encodeAddress($data, 32));
                break;

            case 'Index':
                // AccountIndex as Compact
                $compact = new Compact($this->registry);
                $result = $result->concat($compact->encode($data));
                break;

            case 'Raw':
                // Vec<u8> length-prefixed bytes
                $bytes = new BytesType($this->registry);
                $result = $result->concat($bytes->encode($data));
                break;

            case 'Address20':
                // 20-byte address (Ethereum)
                $result = $result->concat($this->encodeAddress($data, 20));
                break;
        }

        return $result;
    }

    /**
     * Encode an address of given length.
     *
     * @param string|array $address
     * @param int $length
     * @return ScaleBytes
     */
    private function encodeAddress(string|array $address, int $length): ScaleBytes
    {
        if (is_string($address) && str_starts_with($address, '0x')) {
            $hex = substr($address, 2);
            if (strlen($hex) !== $length * 2) {
                throw new ScaleEncodeException(sprintf(
                    'Address length mismatch: expected %d bytes, got %d',
                    $length,
                    strlen($hex) / 2
                ));
            }
            $bytes = array_map('hexdec', str_split($hex, 2));
        } elseif (is_array($address)) {
            if (count($address) !== $length) {
                throw new ScaleEncodeException(sprintf(
                    'Address length mismatch: expected %d bytes, got %d',
                    $length,
                    count($address)
                ));
            }
            $bytes = $address;
        } else {
            throw ScaleEncodeException::invalidType('Address', $address);
        }

        return ScaleBytes::fromBytes($bytes);
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): array
    {
        $index = $bytes->readByte();

        if (!isset($this->indexToName[$index])) {
            throw ScaleDecodeException::invalidEnumVariant($index, array_keys($this->indexToName));
        }

        $variant = $this->indexToName[$index];

        switch ($variant) {
            case 'Id':
            case 'Address32':
                $rawBytes = $bytes->readBytes(32);
                return [$variant => '0x' . bin2hex(pack('C*', ...$rawBytes))];

            case 'Index':
                $compact = new Compact($this->registry);
                return [$variant => $compact->decode($bytes)];

            case 'Raw':
                $bytesType = new BytesType($this->registry);
                return [$variant => $bytesType->decode($bytes)];

            case 'Address20':
                $rawBytes = $bytes->readBytes(20);
                return [$variant => '0x' . bin2hex(pack('C*', ...$rawBytes))];
        }

        throw new ScaleDecodeException(sprintf('Unknown MultiAddress variant: %s', $variant));
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if (!is_array($value) || count($value) !== 1) {
            return false;
        }

        $variant = array_key_first($value);
        return isset($this->nameToIndex[$variant]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'MultiAddress';
    }
}