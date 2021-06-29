<?php

namespace Codec\Test;

use BitWasp\Buffertools\ByteOrder;
use Codec\Utils;
use PHPUnit\Framework\TestCase;
use Codec\ScaleBytes;
use BitWasp\Buffertools\Types\Uint128;
use BitWasp\Buffertools\Parser;

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

    public function testBigDecimal ()
    {
        $u128 = new Uint128(ByteOrder::LE);
        $this->assertEquals(739571955075788261, $u128->readBits(new Parser("e52d2254c67c430a0000000000000000")));

    }

    public function testDechex ()
    {
        $this->assertEquals("c", dechex(12));
        $this->assertEquals("64", dechex(100));
    }


    public function testPadLeft ()
    {
        $this->assertEquals(Utils::padLeft("e52d2254c67c43", 64), "00000000000000000000000000000000000000000000000000e52d2254c67c43");
    }

    public function testBlake2b ()
    {
        $this->assertEquals("23b9bcedca506e32edae5afeda21087f25999546474a4b8298411925150ddeb8",
            sodium_bin2hex(sodium_crypto_generichash(Utils::hex2String("9904042d00000000ef"))));
    }
}


