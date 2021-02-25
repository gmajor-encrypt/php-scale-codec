<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;

class U64 extends Uint
{
    function decode ()
    {
        return Utils::bytesToLittleInt($this->nextBytes(8));
    }

    function encode ($param)
    {
        $value = intval($param);
        if ($value >= 0 && $value <= 2 ** 64 - 1) {
            return Utils::LittleIntToBytes($value, 8);
        }
        return new \InvalidArgumentException(sprintf('%s range out U64', $value));
    }
}


