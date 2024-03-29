<?php

namespace Codec\Test;

use PHPUnit\Framework\TestCase;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use Codec\Base;

final class CompactTest extends TestCase
{

    public function testCompact ()
    {
        $codec = new ScaleInstance(Base::create());
        // gmp data
        $this->assertEquals("0100", $codec->createTypeByTypeString("option<Compact<u32>>")->encode(0));
        $this->assertEquals("04", $codec->createTypeByTypeString("Compact")->encode(gmp_init("1")));
        // u8
        $this->assertEquals("fd03", $codec->createTypeByTypeString("Compact")->encode(2 ** 8 - 1));
        $this->assertEquals(0, gmp_cmp(gmp_sub(gmp_pow("2", 8), 1), $codec->process("Compact", new ScaleBytes("fd03"))));
        // u16
        $this->assertEquals("feff0300", $codec->createTypeByTypeString("Compact")->encode(2 ** 16 - 1));
        $this->assertEquals(0, gmp_cmp(gmp_sub(gmp_pow("2", 16), 1), $codec->process("Compact", new ScaleBytes("feff0300"))));
        // u32
        $this->assertEquals("03ffffffff", $codec->createTypeByTypeString("Compact")->encode(gmp_sub(gmp_pow("2", 32), 1)));
        $this->assertEquals("4294967295", $codec->process("Compact<u32>", new ScaleBytes("03ffffffff")));
        // u64
        $this->assertEquals("13ffffffffffffffff", $codec->createTypeByTypeString("Compact")->encode(gmp_sub(gmp_pow("2", 64), 1)));
        $this->assertEquals(0, gmp_cmp(gmp_sub(gmp_pow("2", 64), 1), $codec->process("Compact", new ScaleBytes("13ffffffffffffffff"))));
        // u128
        $this->assertEquals("33ffffffffffffffffffffffffffffffff", $codec->createTypeByTypeString("Compact")->encode(gmp_sub(gmp_pow("2", 128), 1)));
        $this->assertEquals(0, gmp_cmp(gmp_sub(gmp_pow("2", 128), 1), $codec->process("Compact", new ScaleBytes("33ffffffffffffffffffffffffffffffff"))));
        // u256
        $this->assertEquals("73ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff", $codec->createTypeByTypeString("Compact")->encode(gmp_sub(gmp_pow("2", 256), 1)));
        $this->assertEquals(0, gmp_cmp(gmp_sub(gmp_pow("2", 256), 1), $codec->process("Compact", new ScaleBytes("73ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff"))));
        // >u512
        $this->assertEquals("ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff",
            $codec->createTypeByTypeString("Compact")->encode(gmp_sub(gmp_pow("2", 536), 1)));
        $decoded = $codec->process("Compact", new ScaleBytes("ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff"));
        $this->assertEquals(0, gmp_cmp(gmp_sub(gmp_pow("2", 536), 1), $decoded));
        // >u512 - test LE order
        $this->assertEquals("ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff3f",
            $codec->createTypeByTypeString("Compact")->encode(gmp_sub(gmp_pow("2", 534), 1)));
        $decoded = $codec->process("Compact", new ScaleBytes("ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff3f"));
        $this->assertEquals(0, gmp_cmp(gmp_sub(gmp_pow("2", 534), 1), $decoded));

        // check out of range > 2**536-1
        $this->expectException(\OutOfRangeException::class);
        $codec->createTypeByTypeString("Compact")->encode(gmp_pow("2", 536));
    }

}

