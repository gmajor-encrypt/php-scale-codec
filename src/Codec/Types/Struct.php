<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;

class Struct extends ScaleDecoder
{

    function decode()
    {
        $result = array();
        foreach ($this->typeStruct as $index => $item) {
            $result[$index] = $this->process($item);
        }
        return $result;
    }
}