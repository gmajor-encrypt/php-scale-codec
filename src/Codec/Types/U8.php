<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;

class U8 extends Uint
{
    function decode()
    {
        return $this->nextU8();
    }
}


