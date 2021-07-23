<?php

namespace Codec\Test;

use Codec\Base;
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
            "f" => ["_set" => ["_bitLength" => 64, "f1" => 1, "f2" => 2, "f3" => 4, "f4" => 8]],
            // error
            "g" => "constant"
        ]);
        $this->assertFalse(is_null($generator->getRegistry("a")));
        $this->assertFalse(is_null($generator->getRegistry("b")));
        $this->assertFalse(is_null($generator->getRegistry("c")));
        $this->assertFalse(is_null($generator->getRegistry("d")));
        $this->assertFalse(is_null($generator->getRegistry("e")));
        $this->assertFalse(is_null($generator->getRegistry("f")));
        // Because ``constant`` not registered, so import g failed,
        $this->assertTrue(is_null($generator->getRegistry("g")));

        // Register``constant`` first, so import g success
        Base::regCustom($generator, ["constant" => "u32"]);
        Base::regCustom($generator, ["g" => "constant"]);
        $this->assertFalse(is_null($generator->getRegistry("g")));
    }

}