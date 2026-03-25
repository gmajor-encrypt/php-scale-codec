<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Benchmark;

use Substrate\ScaleCodec\Extrinsic\{ExtrinsicBuilder, ExtrinsicEncoder, ExtrinsicDecoder};

/**
 * @BeforeMethods("setUp")
 * @Iterations(10)
 * @Revs(50)
 * @Warmup(2)
 */
class ExtrinsicBench
{
    private ExtrinsicEncoder $encoder;
    private ExtrinsicDecoder $decoder;
    private string $signer;
    private string $signature;

    public function setUp(): void
    {
        $this->encoder = new ExtrinsicEncoder();
        $this->decoder = new ExtrinsicDecoder();
        $this->signer = '0x' . str_repeat('01', 32);
        $this->signature = '0x' . str_repeat('aa', 64);
    }

    public function benchBuildUnsignedExtrinsic(): void
    {
        ExtrinsicBuilder::create()
            ->pallet('System')
            ->function('remark')
            ->palletIndex(0)
            ->functionIndex(0)
            ->buildUnsigned();
    }

    public function benchBuildSignedExtrinsic(): void
    {
        ExtrinsicBuilder::create()
            ->pallet('Balances')
            ->function('transfer')
            ->palletIndex(5)
            ->functionIndex(0)
            ->signer($this->signer)
            ->signature($this->signature)
            ->nonce(1)
            ->tip(0)
            ->build();
    }

    public function benchEncodeUnsignedExtrinsic(): void
    {
        $extrinsic = ExtrinsicBuilder::create()
            ->pallet('System')
            ->function('remark')
            ->palletIndex(0)
            ->functionIndex(0)
            ->buildUnsigned();

        $this->encoder->encode($extrinsic);
    }

    public function benchEncodeSignedExtrinsic(): void
    {
        $extrinsic = ExtrinsicBuilder::create()
            ->pallet('Balances')
            ->function('transfer')
            ->palletIndex(5)
            ->functionIndex(0)
            ->signer($this->signer)
            ->signature($this->signature)
            ->nonce(1)
            ->tip(0)
            ->build();

        $this->encoder->encode($extrinsic);
    }

    public function benchDecodeExtrinsic(): void
    {
        $hex = '0x0404000000';
        $this->decoder->decodeHex($hex);
    }
}
