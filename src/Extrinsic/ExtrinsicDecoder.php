<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Extrinsic;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Metadata\Metadata;
use Substrate\ScaleCodec\Types\{TypeRegistry, Compact};

/**
 * Decoder for Substrate extrinsics.
 */
class ExtrinsicDecoder
{
    private TypeRegistry $registry;

    /**
     * Create a new extrinsic decoder.
     */
    public function __construct(?TypeRegistry $registry = null)
    {
        $this->registry = $registry ?? new TypeRegistry();
    }

    /**
     * Decode a SCALE-encoded extrinsic.
     *
     * @param ScaleBytes $bytes The encoded bytes
     * @return Extrinsic The decoded extrinsic
     */
    public function decode(ScaleBytes $bytes): Extrinsic
    {
        // Read length
        $compact = new Compact($this->registry);
        $length = $compact->decode($bytes);

        // Read version byte
        $versionByte = $bytes->readByte();
        $isSigned = ($versionByte & 0x80) !== 0;
        $version = $versionByte & 0x7f;

        $signature = null;
        $extra = [];

        if ($isSigned) {
            // Decode signer
            $signer = $this->decodeSigner($bytes);

            // Decode signature
            $signatureBytes = $this->decodeSignature($bytes);

            // Decode extra
            $extra = $this->decodeExtra($bytes);

            $signature = new Signature(
                signer: $signer,
                signature: $signatureBytes,
                extra: $extra,
            );
        }

        // Decode call
        $call = $this->decodeCall($bytes);

        return new Extrinsic(
            call: $call,
            signature: $signature,
            extra: $extra,
        );
    }

    /**
     * Decode hex string to extrinsic.
     */
    public function decodeHex(string $hex): Extrinsic
    {
        if (!str_starts_with($hex, '0x')) {
            $hex = '0x' . $hex;
        }
        return $this->decode(ScaleBytes::fromHex($hex));
    }

    /**
     * Decode the signer address.
     */
    private function decodeSigner(ScaleBytes $bytes): string
    {
        // Read MultiAddress variant
        $variant = $bytes->readByte();

        if ($variant === 0) {
            // Id (32 bytes)
            $addressBytes = $bytes->readBytes(32);
            return '0x' . bin2hex(pack('C*', ...$addressBytes));
        }

        if ($variant === 1) {
            // Index (Compact)
            $compact = new Compact($this->registry);
            return '0x' . dechex($compact->decode($bytes));
        }

        throw new \RuntimeException("Unsupported MultiAddress variant: $variant");
    }

    /**
     * Decode the signature.
     */
    private function decodeSignature(ScaleBytes $bytes): string
    {
        // Sr25519 and Ed25519 are 64 bytes
        $signatureBytes = $bytes->readBytes(64);
        return '0x' . bin2hex(pack('C*', ...$signatureBytes));
    }

    /**
     * Decode extra data.
     */
    private function decodeExtra(ScaleBytes $bytes): array
    {
        $extra = [];

        // Era
        $first = $bytes->peekByte();
        if ($first === 0x00) {
            $bytes->readByte(); // Consume immortal era
            $extra['era'] = ['type' => 'immortal'];
        } else {
            $eraBytes = $bytes->readBytes(2);
            $extra['era'] = [
                'type' => 'mortal',
                'phase' => $eraBytes[0] | ($eraBytes[1] << 8),
            ];
        }

        // Nonce (Compact)
        $compact = new Compact($this->registry);
        $extra['nonce'] = $compact->decode($bytes);

        // Tip (Compact)
        $extra['tip'] = $compact->decode($bytes);

        return $extra;
    }

    /**
     * Decode the call.
     */
    private function decodeCall(ScaleBytes $bytes): array
    {
        $palletIndex = $bytes->readByte();
        $functionIndex = $bytes->readByte();

        // Remaining bytes are the call arguments
        // (In a real implementation, we'd use metadata to decode properly)
        $remainingBytes = $bytes->readBytes($bytes->remaining());

        return [
            'palletIndex' => $palletIndex,
            'functionIndex' => $functionIndex,
            'args' => $remainingBytes,
        ];
    }
}
