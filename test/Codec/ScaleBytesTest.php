<?php

namespace Codec\Test;

use Codec\Utils;
use PHPUnit\Framework\TestCase;
use Codec\ScaleBytes;
use Codec\Types\ScaleDecoder;
use Codec\Base;

final class ScaleBytesTest extends TestCase
{
    public function testNewScaleBytes ()
    {

        $scaleBytes = new ScaleBytes("00");
        $this->assertEquals([0], $scaleBytes->data);
    }

    public function testUint ()
    {
        $generator = Base::create();

        $scaleBytes = new ScaleBytes("64");
        $codec = $generator->U8($scaleBytes);
        $this->assertEquals(100, $codec->decode());

        $encode = $generator->U8();
        $this->assertEquals("64", $encode->encode(100));


        $scaleBytes = new ScaleBytes("0300");
        $codec = $generator->U16($scaleBytes);
        $this->assertEquals(3, $codec->decode());

        $encode = $generator->U16();
        $this->assertEquals("0300", $encode->encode(3));


        $scaleBytes = new ScaleBytes("64000000");
        $codec = $generator->U32($scaleBytes);
        $this->assertEquals(100, $codec->decode());

        $encode = $generator->U32();
        $this->assertEquals("64000000", $encode->encode(100));
    }


    public function testAddressDecode ()
    {
        $scaleBytes = new ScaleBytes("ff1fa9d1bd1db014b65872ee20aee4fd4d3a942d95d3357f463ea6c799130b6318");
        $generator = Base::create();
        $codec = $generator->Address($scaleBytes);
        $codec->decode();
        $this->assertEquals("1fa9d1bd1db014b65872ee20aee4fd4d3a942d95d3357f463ea6c799130b6318", $codec->value["account_id"]);
    }


    public function testBytesToLittleInt ()
    {
        $this->assertEquals(Utils::bytesToLittleInt(Utils::hexToBytes("fc")), 252);
        $this->assertEquals(Utils::bytesToLittleInt(Utils::hexToBytes("fdff")), 65533);
        $this->assertEquals(Utils::bytesToLittleInt(Utils::hexToBytes("feffffff")), 4294967294);
        $this->assertEquals(Utils::bytesToLittleInt(Utils::hexToBytes("ffffffff00000000")), 4294967295);
    }

    public function testCompact ()
    {
        $generator = Base::create();

        $scaleBytes = new ScaleBytes("04");
        $codec = $generator->CompactU32($scaleBytes);
        $this->assertEquals(1, $codec->decode());

        $encode = $generator->Compact();
        $this->assertEquals("fc", $encode->encode(63));


        $scaleBytes2 = new ScaleBytes("c15d");
        $codec = $generator->CompactU32($scaleBytes2);
        $this->assertEquals(6000, $codec->decode());
        $this->assertEquals("c15d", $encode->encode(6000));

        $scaleBytes4 = new ScaleBytes("02093d00");
        $codec = $generator->CompactU32($scaleBytes4);
        $this->assertEquals(1000000, $codec->decode());
        $this->assertEquals("02093d00", $encode->encode(1000000));

        $this->assertEquals("130080cd103d71bc22", $encode->encode(2503000000000000000));
    }

    public function testOptionNull ()
    {
        $generator = Base::create();

        $scaleBytes = new ScaleBytes("00");
        $codec = $generator->Option($scaleBytes);
        $this->assertEquals(null, $codec->decode());

        $encode = $generator->Option();
        $this->assertEquals("00", $encode->encode(null));

        $codec = new ScaleDecoder($generator);
        $codec = $codec->createTypeByTypeString("option<Compact<u32>>");
        $this->assertEquals("01fc", $codec->encode(63));
    }


    public function testString ()
    {
        $generator = Base::create();

        $scaleBytes = new ScaleBytes("1054657374");
        $codec = $generator->String($scaleBytes);
        $this->assertEquals("Test", $codec->decode());
        $encode = $generator->String();
        $this->assertEquals("1054657374", $encode->encode("Test"));
    }


    public function testHexBytes(){
        $generator = Base::create();

        $scaleBytes = new ScaleBytes("18ffff");
        $codec = $generator->HexBytes($scaleBytes);
        $this->assertEquals("0xffff", $codec->decode());
        $encode = $generator->HexBytes();
        $this->assertEquals("18ffff", $encode->encode("0xffff"));
    }
}


