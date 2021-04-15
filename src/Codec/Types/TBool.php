<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;

class TBool extends ScaleInstance
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