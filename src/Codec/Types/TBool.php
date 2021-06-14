<?php

namespace Codec\Types;

class TBool extends ScaleInstance
{

    /**
     * TBool is scale bool type
     *
     * @return bool
     */
    public function decode(): bool
    {
        return $this->nextBool();
    }

    /**
     * @param mixed $param
     * @return string
     */
    public function encode($param): string
    {
        return $param == true ? "01" : "00";
    }
}