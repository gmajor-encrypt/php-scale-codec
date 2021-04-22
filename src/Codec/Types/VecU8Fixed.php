<?php

namespace Codec\Types;

use Codec\Types\ScaleInstance;
use Codec\Utils;

class VecU8Fixed extends ScaleInstance
{

    function decode ()
    {
        return $this->nextBytes($this->FixedLength);
    }


    /**
     * @param array $param
     * @return mixed|string|null
     */
    function encode ($param)
    {
        $value = "";
        if (!is_array($param)) {
            throw new \InvalidArgumentException(sprintf('param not array'));
        }
        foreach ($param as $index => $item) {
            $value .= $this->createTypeByTypeString(sprintf("U8"))->encode($item);
        }
        return $value;
    }
}