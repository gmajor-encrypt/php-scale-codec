<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;

class TBool extends ScaleDecoder
{

    /**
     * TBool is scale bool type
     *
     * @return bool
     */
    function decode()
    {
        return $this->nextBool();
    }

    /**
     * @param mixed $param
     * @return string
     */
    function encode($param)
    {
        return $param == true ? "01" : "00";
    }
}