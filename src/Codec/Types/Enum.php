<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;

class Enum extends ScaleDecoder
{
    /**
     * Enum $value list
     * @var array
     */
    protected $valueList;

    function decode()
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
}