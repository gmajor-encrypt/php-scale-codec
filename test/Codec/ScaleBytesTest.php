<?php

namespace Codec\Test;

use Codec\Utiles;
use PHPUnit\Framework\TestCase;
use Codec\ScaleBytes;
use Codec\Base;

class ScaleBytesTest extends TestCase
{
    public function testNewScaleBytes()
    {
        $scaleBytes = new ScaleBytes("00");
        $this->assertEquals([1 => 0], $scaleBytes->data);
    }

    public function testDecode()
    {
        $scaleBytes = new ScaleBytes("ff1fa9d1bd1db014b65872ee20aee4fd4d3a942d95d3357f463ea6c799130b6318");
        $generator = Base::create();
        $codec = $generator->Address($scaleBytes);
        $codec->decode();
        $this->assertEquals("1fa9d1bd1db014b65872ee20aee4fd4d3a942d95d3357f463ea6c799130b6318", $codec->value["account_id"]);
    }
}


