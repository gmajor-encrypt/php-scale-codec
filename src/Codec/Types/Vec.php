<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;

class Vec extends ScaleDecoder
{

    function decode ()
    {
        $VecLength = $this->process("CompactU32", $this->data);
        $value = [];
        for ($i = 0; $i < $VecLength; $i++) {
            array_push($value, $this->process($this->subType));
        }
        return $value;
    }
}