<?php

namespace Codec\Types;

use InvalidArgumentException;

class VecU8Fixed extends ScaleInstance
{

    function decode (): array
    {
        return $this->nextBytes($this->FixedLength);
    }


    /**
     * @param array $param
     * @return mixed|string|null
     */
    function encode ($param):string
    {
        $value = "";
        if (!is_array($param)) {
            throw new InvalidArgumentException(sprintf('param not array'));
        }
        foreach ($param as $index => $item) {
            $value .= $this->createTypeByTypeString(sprintf("U8"))->encode($item);
        }
        return $value;
    }
}