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


}


