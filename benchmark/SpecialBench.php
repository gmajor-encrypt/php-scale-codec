<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Benchmark;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, BytesType, StringType, AccountIdType};

/**
 * @BeforeMethods("setUp")
 * @Iterations(10)
 * @Revs(100)
 * @Warmup(2)
 */
class SpecialBench
{
    private TypeRegistry $registry;
    private BytesType $bytes;
    private StringType $string;
    private AccountIdType $accountId;

    public function setUp(): void
    {
        $this->registry = new TypeRegistry();
        $this->bytes = new BytesType($this->registry);
        $this->string = new StringType($this->registry);
        $this->accountId = new AccountIdType($this->registry);
    }

    public function benchEncodeBytesSmall(): void
    {
        $this->bytes->encode('0x0102030405');
    }

    public function benchEncodeBytesLarge(): void
    {
        $data = '0x' . str_repeat('aa', 100);
        $this->bytes->encode($data);
    }

    public function benchDecodeBytesSmall(): void
    {
        $bytes = ScaleBytes::fromHex('0x140102030405');
        $this->bytes->decode($bytes);
    }

    public function benchEncodeStringShort(): void
    {
        $this->string->encode('hello');
    }

    public function benchEncodeStringLong(): void
    {
        $this->string->encode(str_repeat('a', 100));
    }

    public function benchDecodeString(): void
    {
        $bytes = ScaleBytes::fromHex('0x1468656c6c6f');
        $this->string->decode($bytes);
    }

    public function benchEncodeAccountId(): void
    {
        $address = '0x' . str_repeat('01', 32);
        $this->accountId->encode($address);
    }

    public function benchDecodeAccountId(): void
    {
        $bytes = ScaleBytes::fromHex('0x' . str_repeat('aa', 32));
        $this->accountId->decode($bytes);
    }
}
