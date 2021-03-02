<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;

class Enum extends ScaleDecoder
{
    /**
     * Enum $value list
     *
     * @var array
     */
    protected $valueList;

    function decode ()
    {
        $index = Utils::byteArray2String($this->nextBytes(1));

        if (!empty($this->typeStruct)) {
            if (count($this->typeStruct) > $index) {
                $enumSubType = $this->typeStruct[$index];
                return $this->process("Struct", $this->data, ["typeStruct" => $enumSubType]);
            }
            throw new \InvalidArgumentException(sprintf('%s range out enum', $index));
        } else {
            if (count($this->valueList) > $index) {
                return $this->valueList[$index];
            }
            throw new \InvalidArgumentException(sprintf('%s range out enum', $index));
        }
    }

    function encode ($param)
    {
        if (!empty($this->typeStruct)) {
            if (!is_array($param)) {
                return new \InvalidArgumentException(sprintf('%v not array', $param));
            }
            foreach ($param as $enumKey => $enumValue) {
                foreach ($this->typeStruct as $index => $item) {
                    if ($index == $enumKey) {
                        $instant = $this->createTypeByTypeString($item);
                        return $instant->encode($param);
                    }
                }
            }
        } else {
            foreach ($this->valueList as $index => $item) {
                if ($item == $index) {
                    return dechex($index);
                }
            }
        }
        return new \InvalidArgumentException(sprintf('%v not present in value', $param));
    }
}