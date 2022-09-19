<?php

namespace Codec\Test;

use PHPUnit\Framework\TestCase;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use Codec\Base;

final class UintTest extends TestCase
{

    public function testU8int ()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals(100, $codec->process("U8", new ScaleBytes("64")));
        $this->assertEquals("64", $codec->createTypeByTypeString("U8")->encode(100));
        $this->expectException(\InvalidArgumentException::class);
        $codec->createTypeByTypeString("U8")->encode(300);
    }

    public function testU16int ()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals(3, $codec->process("U16", new ScaleBytes("0300")));
        $this->assertEquals("0300", $codec->createTypeByTypeString("U16")->encode(3));
        $this->expectException(\InvalidArgumentException::class);
        $codec->createTypeByTypeString("U16")->encode(70000);
    }


    public function testU32int ()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals(100, $codec->process("U32", new ScaleBytes("64000000")));
        $this->assertEquals("64000000", $codec->createTypeByTypeString("U32")->encode(100));
        $this->expectException(\InvalidArgumentException::class);
        $codec->createTypeByTypeString("U32")->encode(8589934592);
    }

    public function testU64int ()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals(184467440737095, $codec->process("U64", new ScaleBytes("471b47acc5a70000")));
        $this->assertEquals("471b47acc5a70000", $codec->createTypeByTypeString("U64")->encode(184467440737095));
        $this->expectException(\InvalidArgumentException::class);
        $codec->createTypeByTypeString("U64")->encode("18446744073709552000");
    }


    public function testU128 ()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals(0, gmp_cmp(gmp_init("739571955075788261"), $codec->process("U128", new ScaleBytes("e52d2254c67c430a0000000000000000"))));
        $codec = $codec->createTypeByTypeString("U128");
        $this->assertEquals("e52d2254c67c430a0000000000000000", $codec->encode(739571955075788261));
        $this->assertEquals("ffffffffffffffffffffffffffffffff", $codec->encode("340282366920938463463374607431768211455"));

    }
}


