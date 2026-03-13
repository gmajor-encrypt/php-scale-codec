<?php

namespace Codec\Types;

use Codec\Utils;
use InvalidArgumentException;

// Basic integers are encoded using a fixed-width little-endian (LE) format.
// unsigned 32-bit integer
// https://substrate.dev/docs/en/knowledgebase/advanced/codec#fixed-width-integers

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


