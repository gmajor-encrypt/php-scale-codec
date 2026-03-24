<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Benchmark;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, Compact};

/**
 * @BeforeMethods("setUp")
 * @Iterations(10)
 * @Revs(100)
 * @Warmup(2)
 */
class CompactBench
{
    private TypeRegistry $registry;
    private Compact $compact;

    public function setUp(): void
    {
        $this->registry = new TypeRegistry();
        $this->compact = new Compact($this->registry);
    }

    public function benchEncodeCompactSmall(): void
    {
        $this->compact->encode(42);
    }

    public function benchEncodeCompactMedium(): void
    {
        $this->compact->encode(10000);
    }

    public function benchEncodeCompactLarge(): void
    {
        $this->compact->encode(1000000000);
    }

    public function benchEncodeCompactBigInt(): void
    {
        $this->compact->encode('340282366920938463463374607431768211455');
    }

    public function benchDecodeCompactSmall(): void
    {
        $bytes = ScaleBytes::fromHex('0xa8'); // 42 << 2
        $this->compact->decode($bytes);
    }

    public function benchDecodeCompactMedium(): void
    {
        $bytes = ScaleBytes::fromHex('0x0101'); // 64
        $this->compact->decode($bytes);
    }

    public function benchRoundTripCompact(): void
    {
        $encoded = $this->compact->encode(12345);
        $this->compact->decode(ScaleBytes::fromBytes($encoded->toBytes()));
    }
}
