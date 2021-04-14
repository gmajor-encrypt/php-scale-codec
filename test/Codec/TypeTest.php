<?php

namespace Codec\Test;

use Codec\Utils;
use PHPUnit\Framework\TestCase;
use Codec\ScaleBytes;
use Codec\Types\ScaleDecoder;
use Codec\Base;

final class TypeTest extends TestCase
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
        $this->assertEquals("1fa9d1bd1db014b65872ee20aee4fd4d3a942d95d3357f463ea6c799130b6318", $codec->decode());
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
        $codec = new ScaleDecoder($generator);
        $this->assertEquals("fc", $generator->Compact()->encode(63));
        $this->assertEquals("02093d00", $generator->Compact()->encode(1000000));
        $this->assertEquals("130080cd103d71bc22", $generator->Compact()->encode(2503000000000000000));
        $this->assertEquals(1, $codec->process("Compact<u32>", new ScaleBytes("04")));
        $this->assertEquals(1000000, $codec->process("Compact<u32>", new ScaleBytes("02093d00")));
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


    public function testBytes ()
    {
        $generator = Base::create();

        $scaleBytes = new ScaleBytes("08ffff");
        $codec = $generator->Bytes($scaleBytes);
        $this->assertEquals("ffff", $codec->decode());
        $encode = $generator->Bytes();
        $this->assertEquals("08ffff", $encode->encode("0xffff"));
    }


    public function testVec ()
    {
        $generator = Base::create();
        $codec = new ScaleDecoder($generator);
        $value = $codec->process("Vec<u8>", new ScaleBytes("08ffff"));
        $this->assertEquals("ffff", Utils::bytesToHex($value));


        $codec = $codec->createTypeByTypeString("Vec<u8>");
        $this->assertEquals("08ffff", $codec->encode(Utils::hexToBytes("ffff")));

    }

    public function testU128 ()
    {
        $generator = Base::create();
        $codec = new ScaleDecoder($generator);
        $value = $codec->process("U128", new ScaleBytes("e52d2254c67c430a0000000000000000"));
        $this->assertEquals(739571955075788261, $value);

        $codec = $codec->createTypeByTypeString("U128");
        $this->assertEquals("e52d2254c67c430a0000000000000000", $codec->encode(739571955075788261));

    }

    public function testEnum ()
    {
        $generator = Base::create();
        $codec = new ScaleDecoder($generator);
        $value = $codec->process("StorageHasher", new ScaleBytes("05"));
        $this->assertEquals("Twox64Concat", $value);

        $codec = $codec->createTypeByTypeString("StorageHasher");
        $this->assertEquals("05", $codec->encode("Twox64Concat"));

        $value = $codec->process("GenericMultiAddress", new ScaleBytes("00a6659e4c3f22c2aa97d54a36e31ab57a617af62bd43ec62ed570771492069270"));
        $this->assertEquals("a6659e4c3f22c2aa97d54a36e31ab57a617af62bd43ec62ed570771492069270", $value);

        $codec = $codec->createTypeByTypeString("GenericMultiAddress");
        $this->assertEquals("00a6659e4c3f22c2aa97d54a36e31ab57a617af62bd43ec62ed570771492069270", $codec->encode(["Id" => "a6659e4c3f22c2aa97d54a36e31ab57a617af62bd43ec62ed570771492069270"]));

    }

    public function testInt ()
    {
        $generator = Base::create();
        $codec = new ScaleDecoder($generator);
        $value = $codec->process("I16", new ScaleBytes("2efb"));
        $this->assertEquals(-1234, $value);

        $codec = $codec->createTypeByTypeString("I16");
        $this->assertEquals("2efb", $codec->encode(-1234));
    }

    public function testStruct ()
    {
        $generator = Base::create();
        $codec = new ScaleDecoder($generator);
        $codec = $codec->createTypeByTypeString("Struct");
        $codec->typeStruct = ["a" => "Compact<u32>", "b" => "Compact<u32>"];
        $codec->init(new ScaleBytes("0c00"));
        $this->assertEquals(["a" => 3, "b" => 0], $codec->decode());

        $this->assertEquals("0c00", $codec->encode(["a" => 3, "b" => 0]));
    }
}


