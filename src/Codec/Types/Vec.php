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


    function encode ($param)
    {
        if (!is_array($param)) {
            return new \InvalidArgumentException(sprintf('%v not array', $param));
        }

        $instant = $this->createTypeByTypeString("CompactU32");
        $length = $instant->encode(count($param));

        $value = $length;
        foreach ($param as $index => $item) {
            $subInstant = $this->createTypeByTypeString($this->subType);
            $value = $value . $subInstant->encode($item);
        }
        return $value;
    }
}