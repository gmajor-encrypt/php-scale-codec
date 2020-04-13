<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;

class U16 extends Uint
{
    function decode()
    {
        return Utils::bytesToLittleInt($this->nextBytes(2));
    }
}


