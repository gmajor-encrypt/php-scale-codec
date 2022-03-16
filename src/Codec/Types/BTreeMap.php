<?php

namespace Codec\Types;

use InvalidArgumentException;

/**
 * Class BTreeMap
 * @package Codec\Types
 *
 *
 * BTreeMap is similar to an array, but its key is not fixed, so the key also needs to be parsed
 * like BTreeMap<type1,type2> -> BTreeMap<string,u64>
 *
 */

class BTreeMap extends ScaleInstance
{

    public function decode(): array
    {
        // length
        $VecLength = $this->process("Compact", $this->data);
        $value = [];
        for ($i = 0; $i < $VecLength; $i++) {
            $subType = explode(",", $this->subType);
            if (count($subType) != 2) {
                throw new InvalidArgumentException(sprintf('%s sub_type invalid', $this->typeString));
            }
            // process key
            $key = $this->process($subType[0]);
            // process value
            $value[$key] = $this->process($subType[1]);
        }
        return $value;
    }

    public function encode($param)
    {
        if (!is_array($param)) {
            return new InvalidArgumentException(sprintf('%s not array', $param));
        }

        $instant = $this->createTypeByTypeString("Compact");
        $length = $instant->encode(count($param));
        $subData = "";

        foreach ($param as $index => $item) {
            $subType = explode(",", $this->subType);
            if (count($subType) != 2) {
                throw new InvalidArgumentException(sprintf('%s sub_type invalid', $this->subType));
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
