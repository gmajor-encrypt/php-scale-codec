<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;

class Tuples extends ScaleInstance
{

    function decode ()
    {
        $result = array();
        foreach ($this->typeStruct as $index => $item) {
            array_push($result, $this->process($item));
        }
        return $result;
    }

    function encode ($param)
    {
        $value = "";
        foreach ($this->typeStruct as $index => $dataType) {
            $subInstant = $this->createTypeByTypeString($dataType);
            $value = $value . $subInstant->encode($param[$index]);
        }
        return $value;
    }

}