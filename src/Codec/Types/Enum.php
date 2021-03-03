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
        $EnumIndex = hexdec(Utils::bytesToHex($this->nextBytes(1)));
        if (!empty($this->typeStruct)) {
//          $typeStruct = ["a" => "string", "b" => "bytes"];
            if (count($this->typeStruct) > $EnumIndex) {
                $index = 0;
                foreach ($this->typeStruct as $key => $item) {
                    if ($EnumIndex == $index) {
                        return $this->process($item);
                    }
                    $index++;
                }
            }
            throw new \InvalidArgumentException(sprintf('%s range out enum', $EnumIndex));
        } else {
            if (count($this->valueList) > $EnumIndex) {
                return $this->valueList[$EnumIndex];
            }
            throw new \InvalidArgumentException(sprintf('%s range out enum', $EnumIndex));
        }
    }

    function encode ($param)
    {
        if (!empty($this->typeStruct)) {
            if (!is_array($param)) {
                return new \InvalidArgumentException(sprintf('%v not array', $param));
            }
            foreach ($param as $enumKey => $enumValue) {
                $index = 0;
                foreach ($this->typeStruct as $key => $item) {
                    if ($key == $enumKey) {
                        $instant = $this->createTypeByTypeString($item);
                        return Utils::LittleIntToBytes($index, 1) . $instant->encode($enumValue);
                    }
                    $index++;
                }
            }
        } else {
            foreach ($this->valueList as $index => $item) {
                if ($param === $item) {
                    return Utils::LittleIntToBytes($index, 1);
                }
            }
        }
        return new \InvalidArgumentException(sprintf('%v not present in value', $param));
    }
}