<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Extrinsic;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Metadata\Metadata;
use Substrate\ScaleCodec\Types\{TypeRegistry, Compact};

/**
 * Represents a Substrate extrinsic (transaction).
 */
class Extrinsic
{
    /**
     * @param array $call The call data [pallet, function, args]
     * @param Signature|null $signature The signature (null for unsigned)
     * @param array $extra Extra data (era, nonce, tip)
     */
    public function __construct(
        public readonly array $call,
        public readonly ?Signature $signature = null,
        public readonly array $extra = [],
    ) {}

    /**
     * Check if this extrinsic is signed.
     */
    public function isSigned(): bool
    {
        return $this->signature !== null;
    }

    /**
     * Get the pallet name.
     */
    public function getPallet(): string
    {
        return $this->call['pallet'] ?? '';
    }

    /**
     * Get the function name.
     */
    public function getFunction(): string
    {
        return $this->call['function'] ?? '';
    }

    /**
     * Get the call arguments.
     */
    public function getArguments(): array
    {
        return $this->call['args'] ?? [];
    }

    /**
     * Get the signer address (if signed).
     */
    public function getSigner(): ?string
    {
        return $this->signature?->signer;
    }

    /**
     * Get the signature bytes (if signed).
     */
    public function getSignatureBytes(): ?string
    {
        return $this->signature?->signature;
    }

    /**
     * Get the era (mortality) of the extrinsic.
     */
    public function getEra(): ?array
    {
        return $this->extra['era'] ?? $this->signature?->getEra();
    }

    /**
     * Get the nonce.
     */
    public function getNonce(): int
    {
        return $this->extra['nonce'] ?? $this->signature?->getNonce() ?? 0;
    }

    /**
     * Get the tip.
     */
    public function getTip(): int|string
    {
        return $this->extra['tip'] ?? $this->signature?->getTip() ?? 0;
    }
}
