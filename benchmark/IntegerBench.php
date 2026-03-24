<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Benchmark;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, TypeFactory, U8, U32, U64, U128, Compact};

/**
 * @BeforeMethods("setUp")
 * @Iterations(10)
 * @Revs(100)
 * @Warmup(2)
 */
class IntegerBench
{
    private TypeRegistry $registry;
    private U8 $u8;
    private U32 $u32;
    private U64 $u64;
    private U128 $u128;

    public function setUp(): void
    {
        $this->registry = new TypeRegistry();
        $this->u8 = new U8($this->registry);
        $this->u32 = new U32($this->registry);
        $this->u64 = new U64($this->registry);
        $this->u128 = new U128($this->registry);
    }

    public function benchEncodeU8(): void
    {
        $this->u8->encode(255);
    }

    public function benchDecodeU8(): void
    {
        $bytes = ScaleBytes::fromHex('0xff');
        $this->u8->decode($bytes);
    }

    public function benchEncodeU32(): void
    {
        $this->u32->encode(4294967295);
    }

    public function benchDecodeU32(): void
    {
        $bytes = ScaleBytes::fromHex('0xffffffff');
        $this->u32->decode($bytes);
    }

    public function benchEncodeU64(): void
    {
        $this->u64->encode('18446744073709551615');
    }

    public function benchDecodeU64(): void
    {
        $bytes = ScaleBytes::fromHex('0xffffffffffffffff');
        $this->u64->decode($bytes);
    }

    public function benchEncodeU128(): void
    {
        $this->u128->encode('340282366920938463463374607431768211455');
    }

    public function benchDecodeU128(): void
    {
        $bytes = ScaleBytes::fromHex('0xffffffffffffffffffffffffffffffff');
        $this->u128->decode($bytes);
    }

    public function benchRoundTripU32(): void
    {
        $encoded = $this->u32->encode(12345678);
        $this->u32->decode(ScaleBytes::fromBytes($encoded->toBytes()));
    }
}
