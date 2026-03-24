<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Extrinsic;

/**
 * Represents an extrinsic signature.
 */
class Signature
{
    /**
     * @param string $signer Signer address (hex string)
     * @param string $signature Signature bytes (hex string)
     * @param array $extra Extra data (era, nonce, tip)
     * @param string $signerType Signer type: 'ed25519', 'sr25519', 'ecdsa'
     */
    public function __construct(
        public readonly string $signer,
        public readonly string $signature,
        public readonly array $extra = [],
        public readonly string $signerType = 'sr25519',
    ) {}

    /**
     * Check if this is an Ed25519 signature.
     */
    public function isEd25519(): bool
    {
        return $this->signerType === 'ed25519';
    }

    /**
     * Check if this is an Sr25519 signature.
     */
    public function isSr25519(): bool
    {
        return $this->signerType === 'sr25519';
    }

    /**
     * Check if this is an Ecdsa signature.
     */
    public function isEcdsa(): bool
    {
        return $this->signerType === 'ecdsa';
    }

    /**
     * Get the era (mortality) of the extrinsic.
     */
    public function getEra(): ?array
    {
        return $this->extra['era'] ?? null;
    }

    /**
     * Get the nonce.
     */
    public function getNonce(): int
    {
        return $this->extra['nonce'] ?? 0;
    }

    /**
     * Get the tip.
     */
    public function getTip(): int|string
    {
        return $this->extra['tip'] ?? 0;
    }
}
