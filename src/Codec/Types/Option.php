<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;

class Option extends ScaleDecoder
{
    function decode()
    {
        $optionData = $this->nextBytes(1);
        if (!empty($this->subType) && Utils::bytesToHex($optionData) != '00') {
            return $this->process($this->subType, $this->data);
        }
        return null;
    }

    function encode($param)
    {
        if(!empty($param) and !empty($this->subType)){
            $instant = $this->createTypeByTypeString($this->subType);
            return "01".$instant->encode($param);
        }
        return "00";
    }
}