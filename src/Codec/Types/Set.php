<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;

class Set extends ScaleInstance
{
    function decode ()
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

    function encode ($param)
    {
        $value = 0;
        if (!is_array($value)) {
            return new \InvalidArgumentException(sprintf('%v not array', $param));
        }
        foreach ($this->valueList as $index => $item) {
            if (in_array($index, $value)) {
                $value += $item;
            }
        }
        $subInstant = $this->createTypeByTypeString("U8");
        return $subInstant->encode($value);
    }
}