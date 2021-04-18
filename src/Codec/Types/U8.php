<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;
use Codec\Utils;

class U8 extends Uint
{
    function decode ()
    {
        return $this->nextU8();
    }


    function encode ($param)
    {
        $value = intval($param);
        if ($value >= 0 && $value <= 2 ** 8 - 1) {
            return Utils::LittleIntToBytes($value, 1);
        }
        throw new \InvalidArgumentException(sprintf('%s range out u8', $value));
    }
}


