<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;

class U32 extends Uint
{
    function decode()
    {
        return Utils::bytesToLittleInt($this->nextBytes(4));
    }
}


