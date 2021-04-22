<?php

namespace Codec\Test;

use Codec\Utils;
use PHPUnit\Framework\TestCase;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use Codec\Base;

final class TypeTest extends TestCase
{
    public function testNewScaleBytes ()
    {

        $scaleBytes = new ScaleBytes("00");
        $this->assertEquals([0], $scaleBytes->data);

        $this->expectDeprecationMessage('"wa" is not a hex string');
        new ScaleBytes("wa");
    }

    public function testAddressDecode ()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals("1fa9d1bd1db014b65872ee20aee4fd4d3a942d95d3357f463ea6c799130b6318",
            $codec->process("Address", new ScaleBytes("ff1fa9d1bd1db014b65872ee20aee4fd4d3a942d95d3357f463ea6c799130b6318")));
        $this->assertEquals("ff1fa9d1bd1db014b65872ee20aee4fd4d3a942d95d3357f463ea6c799130b6318",
            $codec->createTypeByTypeString("Address")->encode("1fa9d1bd1db014b65872ee20aee4fd4d3a942d95d3357f463ea6c799130b6318"));

        $this->expectExceptionMessage("Address not support AccountIndex or param not AccountId");
        $codec->createTypeByTypeString("Address")->encode("fa93");

    }

    public function testOptionNull ()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals(null, $codec->process("Option", new ScaleBytes("00")));
        $this->assertEquals("00", $codec->createTypeByTypeString("Option")->encode(null));
        $this->assertEquals("01fc", $codec->createTypeByTypeString("option<Compact<u32>>")->encode(63));
        $this->assertEquals(true, $codec->process("Option<bool>", new ScaleBytes("01")));
        $this->assertEquals(false, $codec->process("Option<bool>", new ScaleBytes("02")));
        $this->assertEquals("01", $codec->createTypeByTypeString("Option<bool>")->encode(true));
        $this->assertEquals("02", $codec->createTypeByTypeString("Option<bool>")->encode(false));
    }


    public function testString ()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals("Test", $codec->process("String", new ScaleBytes("1054657374")));
        $this->assertEquals("1054657374", $codec->createTypeByTypeString("String")->encode("Test"));
    }


    public function testBytes ()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals("ffff", $codec->process("Bytes", new ScaleBytes("08ffff")));
        $this->assertEquals("08ffff", $codec->createTypeByTypeString("Bytes")->encode("0xffff"));
    }

    public function testVec ()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals("ffff", Utils::bytesToHex($codec->process("Vec<u8>", new ScaleBytes("08ffff"))));
        $this->assertEquals("08ffff", $codec->createTypeByTypeString("Vec<u8>")->encode(Utils::hexToBytes("ffff")));
        $this->assertEquals([
            ["col1" => 716, "col2" => 47054848, "col3" => 0],
            ["col1" => 256, "col2" => 0, "col3" => 0]], $codec->process("Vec<(u32, u32, u16)>", new ScaleBytes("08cc0200000000ce0200000001")));
        $this->assertEquals("1001000000020000000300000004000000", $codec->createTypeByTypeString("Vec<u32>")->encode([1, 2, 3, 4]));
    }



    public function testEnum ()
    {
        $codec = new ScaleInstance(Base::create());

        $codec = $codec->createTypeByTypeString("Enum");
        $codec->valueList = [0, 1, 49, 50];
        $codec->init(new ScaleBytes("02"));
        $this->assertEquals(49, $codec->decode());
        $this->assertEquals("02", $codec->encode(49));

        $this->assertEquals("Twox64Concat", $codec->process("StorageHasher", new ScaleBytes("05")));
        $this->assertEquals("05", $codec->createTypeByTypeString("StorageHasher")->encode("Twox64Concat"));
        $this->assertEquals("a6659e4c3f22c2aa97d54a36e31ab57a617af62bd43ec62ed570771492069270",
            $codec->process("GenericMultiAddress", new ScaleBytes("00a6659e4c3f22c2aa97d54a36e31ab57a617af62bd43ec62ed570771492069270")));
        $this->assertEquals("00a6659e4c3f22c2aa97d54a36e31ab57a617af62bd43ec62ed570771492069270",
            $codec->createTypeByTypeString("GenericMultiAddress")->encode(["Id" => "a6659e4c3f22c2aa97d54a36e31ab57a617af62bd43ec62ed570771492069270"]));

    }

    public function testInt ()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals(-1234, $codec->process("I16", new ScaleBytes("2efb")));
        $this->assertEquals("2efb", $codec->createTypeByTypeString("I16")->encode(-1234));
    }

    public function testStruct ()
    {
        $codec = new ScaleInstance(Base::create());
        $codec = $codec->createTypeByTypeString("Struct");
        $codec->typeStruct = ["a" => "Compact<u32>", "b" => "Compact<u32>"];
        $codec->init(new ScaleBytes("0c00"));
        $this->assertEquals(["a" => 3, "b" => 0], $codec->decode());

        $this->assertEquals("0c00", $codec->encode(["a" => 3, "b" => 0]));
    }
}


