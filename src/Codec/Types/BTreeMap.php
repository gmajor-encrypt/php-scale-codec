<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;

class BTreeMap extends ScaleDecoder
{

    function decode ()
    {
        $VecLength = $this->process("CompactU32", $this->data);
        $value = [];
        for ($i = 0; $i < $VecLength; $i++) {
            $subType = explode($this->subType, ",");
            if (count($subType) != 2) {
                return new \InvalidArgumentException(sprintf('%v sub_type invalid', $this->typeString));
            }
            $key = $this->process($subType[0]);
            array_push($value, [$key => $this->process($subType[1])]);
        }
        return $value;
    }

    function encode ($param)
    {


    }

}
