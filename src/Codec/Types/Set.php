<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;

class Set extends ScaleDecoder
{
    /**
     * Enum $value list
     * @var array
     */
    protected $valueList;

    function decode()
    {
        $setIndex = $this->process("U8");
        $value = array();
        if ($setIndex > 0) {
            foreach ($this->valueList as $index => $item) {
                if ($setIndex & $item > 0) {
                    array_push($value, $index);
                }
            }
        }
        return $value;

    }
}