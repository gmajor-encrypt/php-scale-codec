<?php

namespace Codec\Test;

use Codec\Base;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use PHPUnit\Framework\TestCase;


final class BaseTest extends TestCase
{
    public function testImportCustomType ()
    {
        $generator = Base::create();
        Base::regCustom($generator, [
            // direct
            "a" => "balance",
            // struct
            "b" => ["b1" => "u8", "b2" => "vec<u32>"],
            // enum
            "c" => ["_enum" => ["c1", "c2", "c3"]],
            // tuple
            "d" => "(u32, bool)",
            // fixed
            "e" => "[u32; 5]",
            // set
            "f" => ["_set" => ["_bitLength" => 64, "f1" => 1, "f2" => 2, "f3" => 4, "f4" => 8, "f5" => 16]],
            // error
            "g" => "constant"
        ]);
        $codec = new ScaleInstance($generator);

        // inherit
        $this->assertEquals($codec->process("a", new ScaleBytes($codec->createTypeByTypeString("a")->encode(gmp_init(739571955075788261)))), gmp_init(739571955075788261));
        // struct
        $this->assertEquals($codec->process("b", new ScaleBytes($codec->createTypeByTypeString("b")->encode(["b1" => 1, "b2" => [1, 2]]))), ["b1" => 1, "b2" => [1, 2]]);
        // enum
        $this->assertEquals($codec->process("c", new ScaleBytes($codec->createTypeByTypeString("c")->encode("c2"))), "c2");
        // tuple
        $this->assertEquals($codec->process("d", new ScaleBytes($codec->createTypeByTypeString("d")->encode([1, true]))), [1, true]);
        // fixed
        $this->assertEquals($codec->process("e", new ScaleBytes($codec->createTypeByTypeString("e")->encode([1, 2, 3, 4, 5]))), [1, 2, 3, 4, 5]);
        // set
        $this->assertEquals($codec->process("f", new ScaleBytes($codec->createTypeByTypeString("f")->encode(["f1", "f2"]))), ["f1", "f2"]);


        // Because ``constant`` not registered, so import g failed,
        $this->assertTrue(is_null($generator->getRegistry("g")));

        // Register``constant`` first, so import g success
        Base::regCustom($generator, ["constant" => "u32"]);
        Base::regCustom($generator, ["g" => "constant"]);
        $this->assertFalse(is_null($generator->getRegistry("g")));
    }

}