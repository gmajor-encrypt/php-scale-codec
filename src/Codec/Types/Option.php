<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utiles;

class Option extends ScaleDecoder
{
    function decode()
    {
        $optionData = $this->nextBytes(1);
        if (!empty($this->subType) && Utiles::bytesToHex($optionData) != '00') {
            return $this->process($this->subType, $this->data);
        }
        return null;
    }
}