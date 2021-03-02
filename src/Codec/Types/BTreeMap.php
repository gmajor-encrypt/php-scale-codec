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
        if (!is_array($param)) {
            return new \InvalidArgumentException(sprintf('%v not array', $param));
        }

        $instant = $this->createTypeByTypeString("CompactU32");
        $length = $instant->encode(count($param));
        $subData = "";

        foreach ($param as $index => $item) {
            $subType = explode($this->subType, ",");
            if (count($subType) != 2) {
                return new \InvalidArgumentException(sprintf('%v sub_type invalid', $this->typeString));
            }
            // key
            $subKeyInstant = $this->createTypeByTypeString($subType[0]);
            $subData = $subData . $subKeyInstant->encode($item);

            // value
            $subValueInstant = $this->createTypeByTypeString($subType[1]);
            $subData = $subData . $subValueInstant->encode($item);

        }
        return $length . $subData;

    }

}
