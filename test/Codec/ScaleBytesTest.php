<?php

namespace Codec\Test;

use Codec\Utils;
use PHPUnit\Framework\TestCase;
use Codec\ScaleBytes;

final class ScaleBytesTest extends TestCase
{
    public function testNewScaleBytes ()
    {

        $scaleBytes = new ScaleBytes("00");
        $this->assertEquals([0], $scaleBytes->data);
    }

    public function testBytesToLittleInt ()
    {
        $this->assertEquals(Utils::bytesToLittleInt(Utils::hexToBytes("fc")), 252);
        $this->assertEquals(Utils::bytesToLittleInt(Utils::hexToBytes("fdff")), 65533);
        $this->assertEquals(Utils::bytesToLittleInt(Utils::hexToBytes("feffffff")), 4294967294);
        $this->assertEquals(Utils::bytesToLittleInt(Utils::hexToBytes("ffffffff00000000")), 4294967295);
    }

}


