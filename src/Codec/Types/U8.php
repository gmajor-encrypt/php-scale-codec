<?php

namespace Codec\Types;

use Codec\Utils;
use InvalidArgumentException;

class U8 extends Uint
{
    public function decode (): int
    {
        return $this->nextU8();
    }


    public function encode ($param)
    {
        $value = intval($param);
        if ($value >= 0 && $value <= 2 ** 8 - 1) {
            return Utils::LittleIntToBytes($value, 1);
        }
        throw new InvalidArgumentException(sprintf('%s range out u8', $value));
    }
}


