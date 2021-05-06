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

        $this->assertEquals("fc", $codec->createTypeByTypeString("Compact")->encode(63));
        $this->assertEquals("02093d00", $codec->createTypeByTypeString("Compact")->encode(1000000));
        $this->assertEquals("130080cd103d71bc22", $codec->createTypeByTypeString("Compact")->encode(2503000000000000000));
        $this->assertEquals(1, $codec->process("Compact", new ScaleBytes("04")));
        $this->assertEquals(1000000, $codec->process("Compact", new ScaleBytes("02093d00")));
        // check outof range > 2**536+1
        $this->expectException(\OutOfRangeException::class);
        $codec->createTypeByTypeString("Compact")->encode(2 ** 536 + 1);
    }


}


