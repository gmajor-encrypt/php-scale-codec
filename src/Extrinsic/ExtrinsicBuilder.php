<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Extrinsic;

/**
 * Builder for creating extrinsics.
 *
 * Usage:
 *   $extrinsic = ExtrinsicBuilder::create()
 *       ->pallet('Balances')
 *       ->function('transfer')
 *       ->args(['dest' => $address, 'value' => 1000])
 *       ->signer($signerAddress)
 *       ->signature($signature)
 *       ->nonce(1)
 *       ->tip(0)
 *       ->build();
 */
class ExtrinsicBuilder
{
    private ?string $pallet = null;
    private ?string $function = null;
    private int $palletIndex = 0;
    private int $functionIndex = 0;
    private array $args = [];
    private ?string $signer = null;
    private ?string $signature = null;
    private string $signatureType = 'sr25519';
    private int $nonce = 0;
    private int|string $tip = 0;
    private ?array $era = null;
    private int $version = 4;

    private function __construct()
    {
    }

    /**
     * Create a new builder instance.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Set the pallet name.
     */
    public function pallet(string $name): self
    {
        $this->pallet = $name;
        return $this;
    }

    /**
     * Set the pallet index directly.
     */
    public function palletIndex(int $index): self
    {
        $this->palletIndex = $index;
        return $this;
    }

    /**
     * Set the function name.
     */
    public function function(string $name): self
    {
        $this->function = $name;
        return $this;
    }

    /**
     * Set the function index directly.
     */
    public function functionIndex(int $index): self
    {
        $this->functionIndex = $index;
        return $this;
    }

    /**
     * Set the call arguments.
     */
    public function args(array $args): self
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Add a single argument.
     */
    public function arg(string $name, mixed $value): self
    {
        $this->args[$name] = $value;
        return $this;
    }

    /**
     * Set the signer address.
     */
    public function signer(string $address): self
    {
        $this->signer = $address;
        return $this;
    }

    /**
     * Set the signature.
     */
    public function signature(string $signature, string $type = 'sr25519'): self
    {
        $this->signature = $signature;
        $this->signatureType = $type;
        return $this;
    }

    /**
     * Set the nonce.
     */
    public function nonce(int $nonce): self
    {
        $this->nonce = $nonce;
        return $this;
    }

    /**
     * Set the tip.
     */
    public function tip(int|string $tip): self
    {
        $this->tip = $tip;
        return $this;
    }

    /**
     * Set immortal era.
     */
    public function immortal(): self
    {
        $this->era = null;
        return $this;
    }

    /**
     * Set mortal era.
     */
    public function mortal(int $period, int $phase = 0): self
    {
        $this->era = ['period' => $period, 'phase' => $phase];
        return $this;
    }

    /**
     * Set the extrinsic version.
     */
    public function version(int $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Build an unsigned extrinsic.
     */
    public function buildUnsigned(): Extrinsic
    {
        return new Extrinsic(
            call: $this->buildCall(),
            signature: null,
            extra: [],
        );
    }

    /**
     * Build a signed extrinsic.
     */
    public function build(): Extrinsic
    {
        $signature = null;

        if ($this->signer !== null && $this->signature !== null) {
            $signature = new Signature(
                signer: $this->signer,
                signature: $this->signature,
                extra: [
                    'era' => $this->era,
                    'nonce' => $this->nonce,
                    'tip' => $this->tip,
                ],
                signerType: $this->signatureType,
            );
        }

        return new Extrinsic(
            call: $this->buildCall(),
            signature: $signature,
            extra: [
                'era' => $this->era,
                'nonce' => $this->nonce,
                'tip' => $this->tip,
            ],
        );
    }

    /**
     * Build the call data.
     */
    private function buildCall(): array
    {
        return [
            'pallet' => $this->pallet,
            'palletIndex' => $this->palletIndex,
            'function' => $this->function,
            'functionIndex' => $this->functionIndex,
            'args' => $this->args,
        ];
    }
}
