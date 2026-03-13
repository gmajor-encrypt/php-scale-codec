<?php

namespace Codec\Types;

use Codec\Utils;
use InvalidArgumentException;


// Basic integers are encoded using a fixed-width little-endian (LE) format.
// unsigned 8-bit integer
// https://substrate.dev/docs/en/knowledgebase/advanced/codec#fixed-width-integers
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


