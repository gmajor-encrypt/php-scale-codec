<?php

namespace Codec\Types;

use Codec\Utils;
use InvalidArgumentException;

class U32 extends Uint
{
    public function decode(): int
    {
        return Utils::bytesToLittleInt($this->nextBytes(4));
    }

    public function encode($param)
    {
        $value = intval($param);
        if ($value >= 0 && $value <= 2 ** 32 - 1) {
            return Utils::LittleIntToBytes($value, 4);
        }
        throw new InvalidArgumentException(sprintf('%s range out U32', $value));
    }
}


