<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;
use Codec\Utils;

class U16 extends Uint
{
    function decode ()
    {
        return Utils::bytesToLittleInt($this->nextBytes(2));
    }

    function encode ($param)
    {
        $value = intval($param);
        if ($value >= 0 && $value <= 2 ** 16 - 1) {
            return Utils::LittleIntToBytes($value, 2);
        }
        throw new \InvalidArgumentException(sprintf('%s range out U16', $value));
    }
}


