<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;
use Codec\Utils;

class U32 extends Uint
{
    function decode()
    {
        return Utils::bytesToLittleInt($this->nextBytes(4));
    }

    function encode ($param)
    {
        $value = intval($param);
        if ($value >= 0 && $value <= 2**32 - 1) {
            return Utils::LittleIntToBytes($value, 4);
        }
        return new \InvalidArgumentException(sprintf('%s range out U32', $value));
    }
}


