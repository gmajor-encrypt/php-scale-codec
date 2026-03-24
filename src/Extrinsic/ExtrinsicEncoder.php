<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Extrinsic;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Metadata\Metadata;
use Substrate\ScaleCodec\Types\{TypeRegistry, Compact, U8, U32, U64, U128, BoolType, BytesType, AccountIdType, MultiAddressType};

/**
 * Encoder for Substrate extrinsics.
 */
class ExtrinsicEncoder
{
    private TypeRegistry $registry;

    /**
     * Create a new extrinsic encoder.
     */
    public function __construct(?TypeRegistry $registry = null)
    {
        $this->registry = $registry ?? new TypeRegistry();
    }

    /**
     * Encode an extrinsic to SCALE bytes.
     *
     * @param Extrinsic $extrinsic The extrinsic to encode
     * @param int $version Extrinsic version (default 4)
     * @return ScaleBytes The encoded bytes
     */
    public function encode(Extrinsic $extrinsic, int $version = 4): ScaleBytes
    {
        // Build the call data first
        $callData = $this->encodeCall($extrinsic);

        if ($extrinsic->isSigned()) {
            return $this->encodeSigned($extrinsic, $callData, $version);
        }

        return $this->encodeUnsigned($callData, $version);
    }

    /**
     * Encode a signed extrinsic.
     */
    private function encodeSigned(Extrinsic $extrinsic, ScaleBytes $callData, int $version): ScaleBytes
    {
        $result = ScaleBytes::empty();

        // Version byte: 0x80 | version (indicates signed)
        $versionByte = 0x80 | ($version & 0x7f);
        $result = $result->concat(ScaleBytes::fromBytes([$versionByte]));

        // Signer (MultiAddress)
        $signer = $extrinsic->getSigner();
        $result = $result->concat($this->encodeSigner($signer));

        // Signature
        $signature = $extrinsic->getSignatureBytes();
        $signatureType = $extrinsic->signature?->signerType ?? 'sr25519';
        $result = $result->concat($this->encodeSignature($signature, $signatureType));

        // Extra: era, nonce, tip
        $result = $result->concat($this->encodeExtra($extrinsic));

        // Call data
        $result = $result->concat($callData);

        // Length prefix
        return $this->withLengthPrefix($result);
    }

    /**
     * Encode an unsigned extrinsic.
     */
    private function encodeUnsigned(ScaleBytes $callData, int $version): ScaleBytes
    {
        $result = ScaleBytes::empty();

        // Version byte: just the version (no 0x80 bit = unsigned)
        $result = $result->concat(ScaleBytes::fromBytes([$version & 0x7f]));

        // Call data
        $result = $result->concat($callData);

        // Length prefix
        return $this->withLengthPrefix($result);
    }

    /**
     * Encode the call data.
     */
    private function encodeCall(Extrinsic $extrinsic): ScaleBytes
    {
        $result = ScaleBytes::empty();

        // Pallet index (U8)
        $palletIndex = $extrinsic->call['palletIndex'] ?? 0;
        $result = $result->concat(ScaleBytes::fromBytes([$palletIndex]));

        // Function index (U8)
        $functionIndex = $extrinsic->call['functionIndex'] ?? 0;
        $result = $result->concat(ScaleBytes::fromBytes([$functionIndex]));

        // Arguments
        $args = $extrinsic->getArguments();
        $result = $result->concat($this->encodeArguments($args));

        return $result;
    }

    /**
     * Encode the signer address.
     */
    private function encodeSigner(string $signer): ScaleBytes
    {
        // Assume 32-byte account ID (Id variant = 0)
        $result = ScaleBytes::fromBytes([0]); // MultiAddress::Id

        // Convert hex to bytes
        if (str_starts_with($signer, '0x')) {
            $signer = substr($signer, 2);
        }

        $bytes = array_map('hexdec', str_split($signer, 2));
        return $result->concat(ScaleBytes::fromBytes($bytes));
    }

    /**
     * Encode the signature.
     */
    private function encodeSignature(string $signature, string $type): ScaleBytes
    {
        // Convert hex to bytes
        if (str_starts_with($signature, '0x')) {
            $signature = substr($signature, 2);
        }

        $bytes = array_map('hexdec', str_split($signature, 2));

        // Sr25519 and Ed25519 are 64 bytes, Ecdsa is 65 bytes
        return ScaleBytes::fromBytes($bytes);
    }

    /**
     * Encode the extra data (era, nonce, tip).
     */
    private function encodeExtra(Extrinsic $extrinsic): ScaleBytes
    {
        $result = ScaleBytes::empty();

        // Era (immortal = 0x00, or mortal as 2 bytes)
        $era = $extrinsic->getEra();
        if ($era === null) {
            // Immortal era
            $result = $result->concat(ScaleBytes::fromBytes([0x00]));
        } else {
            // Mortal era: phase + period
            $result = $result->concat($this->encodeEra($era));
        }

        // Nonce (Compact)
        $nonce = $extrinsic->getNonce();
        $compact = new Compact($this->registry);
        $result = $result->concat($compact->encode($nonce));

        // Tip (Compact, usually 0)
        $tip = $extrinsic->getTip();
        $result = $result->concat($compact->encode(is_string($tip) ? $tip : $tip));

        return $result;
    }

    /**
     * Encode era.
     */
    private function encodeEra(array $era): ScaleBytes
    {
        $period = $era['period'] ?? 64;
        $phase = $era['phase'] ?? 0;

        // Simple mortal encoding
        $encoded = $this->encodeMortalEra($period, $phase);
        return ScaleBytes::fromBytes($encoded);
    }

    /**
     * Encode mortal era.
     */
    private function encodeMortalEra(int $period, int $phase): array
    {
        // Simplified: just return phase bytes
        $low = $phase & 0xff;
        $high = ($phase >> 8) & 0xff;
        return [$low, $high];
    }

    /**
     * Encode call arguments.
     */
    private function encodeArguments(array $args): ScaleBytes
    {
        $result = ScaleBytes::empty();

        foreach ($args as $arg) {
            // Simple argument encoding - real implementation would need type info
            if (is_int($arg)) {
                // Encode as U32 for simplicity
                $u32 = new U32($this->registry);
                $result = $result->concat($u32->encode($arg));
            } elseif (is_string($arg) && str_starts_with($arg, '0x')) {
                // Hex bytes
                $hex = substr($arg, 2);
                $bytes = array_map('hexdec', str_split($hex, 2));
                $result = $result->concat(ScaleBytes::fromBytes($bytes));
            } elseif (is_array($arg)) {
                // Nested structure - would need type info
                // For now, skip
            }
        }

        return $result;
    }

    /**
     * Add length prefix to the encoded data.
     */
    private function withLengthPrefix(ScaleBytes $data): ScaleBytes
    {
        $length = count($data->toBytes());
        $compact = new Compact($this->registry);
        $lengthBytes = $compact->encode($length);
        return $lengthBytes->concat($data);
    }
}
