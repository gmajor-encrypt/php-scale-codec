<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Benchmark;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, VecType, OptionType, TupleType, U8, U32};

/**
 * @BeforeMethods("setUp")
 * @Iterations(10)
 * @Revs(50)
 * @Warmup(2)
 */
class CompoundBench
{
    private TypeRegistry $registry;
    private VecType $vecU8;
    private VecType $vecU32;
    private OptionType $optionU32;
    private TupleType $tuple;

    public function setUp(): void
    {
        $this->registry = new TypeRegistry();
        
        $this->vecU8 = new VecType($this->registry);
        $this->vecU8->setElementType(new U8($this->registry));

        $this->vecU32 = new VecType($this->registry);
        $this->vecU32->setElementType(new U32($this->registry));

        $this->optionU32 = new OptionType($this->registry);
        $this->optionU32->setInnerType(new U32($this->registry));

        $this->tuple = new TupleType($this->registry);
        $this->tuple->setElementTypes([new U8($this->registry), new U32($this->registry)]);
    }

    public function benchEncodeVecU8Small(): void
    {
        $this->vecU8->encode([1, 2, 3, 4, 5]);
    }

    public function benchEncodeVecU8Large(): void
    {
        $data = range(0, 255);
        $this->vecU8->encode($data);
    }

    public function benchDecodeVecU8Small(): void
    {
        $bytes = ScaleBytes::fromHex('0x140102030405'); // len=5, then bytes
        $this->vecU8->decode($bytes);
    }

    public function benchEncodeVecU32(): void
    {
        $data = [100, 200, 300, 400, 500];
        $this->vecU32->encode($data);
    }

    public function benchDecodeVecU32(): void
    {
        // len=5 + 5 * 4 bytes
        $hex = '0x14' . str_repeat('64000000', 5);
        $bytes = ScaleBytes::fromHex($hex);
        $this->vecU32->decode($bytes);
    }

    public function benchEncodeOptionSome(): void
    {
        $this->optionU32->encode(42);
    }

    public function benchEncodeOptionNone(): void
    {
        $this->optionU32->encode(null);
    }

    public function benchDecodeOptionSome(): void
    {
        $bytes = ScaleBytes::fromHex('0x012a000000');
        $this->optionU32->decode($bytes);
    }

    public function benchDecodeOptionNone(): void
    {
        $bytes = ScaleBytes::fromHex('0x00');
        $this->optionU32->decode($bytes);
    }

    public function benchEncodeTuple(): void
    {
        $this->tuple->encode([100, 1000]);
    }

    public function benchDecodeTuple(): void
    {
        $bytes = ScaleBytes::fromHex('0x64e8030000');
        $this->tuple->decode($bytes);
    }
}
