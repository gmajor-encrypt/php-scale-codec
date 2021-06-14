<?php

namespace Codec\Types;

use Codec\Utils;
use InvalidArgumentException;

class U16 extends Uint
{
    public function decode(): int
    {
        return Utils::bytesToLittleInt($this->nextBytes(2));
    }

    public function encode($param)
    {
        $value = intval($param);
        if ($value >= 0 && $value <= 2 ** 16 - 1) {
            return Utils::LittleIntToBytes($value, 2);
        }
        throw new InvalidArgumentException(sprintf('%s range out U16', $value));
    }
}


