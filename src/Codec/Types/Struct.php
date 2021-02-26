<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;

class Struct extends ScaleDecoder
{

    function decode ()
    {
        $result = array();
        foreach ($this->typeStruct as $index => $item) {
            $result[$index] = $this->process($item);
        }
        return $result;
    }

    function encode ($param)
    {
        $value = "";
        foreach ($this->typeStruct as $index => $dataType) {
            if (!array_key_exists($index, $param)) {
                return new \InvalidArgumentException(sprintf('%v not in Struct', $index));
            }
            $subInstant = $this->createTypeByTypeString($dataType);
            $value = $value . $subInstant->encode($param[$index]);
        }
    }

}