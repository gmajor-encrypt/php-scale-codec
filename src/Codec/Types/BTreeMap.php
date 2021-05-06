<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;

class BTreeMap extends ScaleInstance
{

    function decode ()
    {
        $VecLength = $this->process("Compact", $this->data);
        $value = [];
        for ($i = 0; $i < $VecLength; $i++) {
            $subType = explode(",", $this->subType);
            if (count($subType) != 2) {
                throw new \InvalidArgumentException(sprintf('%s sub_type invalid', $this->typeString));
            }
            $key = $this->process($subType[0]);
            $value[$key] = $this->process($subType[1]);
        }
        return $value;
    }

    function encode ($param)
    {
        if (!is_array($param)) {
            return new \InvalidArgumentException(sprintf('%s not array', $param));
        }

        $instant = $this->createTypeByTypeString("Compact");
        $length = $instant->encode(count($param));
        $subData = "";

        foreach ($param as $index => $item) {
            $subType = explode(",", $this->subType);
            if (count($subType) != 2) {
                throw new \InvalidArgumentException(sprintf('%s sub_type invalid', $this->typeString));
            }
            // key
            $subKeyInstant = $this->createTypeByTypeString($subType[0]);
            $subData = $subData . $subKeyInstant->encode($index);

            // value
            $subValueInstant = $this->createTypeByTypeString($subType[1]);
            $subData = $subData . $subValueInstant->encode($item);

        }
        return $length . $subData;

    }

}
