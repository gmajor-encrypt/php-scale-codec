<?php

namespace Codec\Types;

use InvalidArgumentException;

/**
 * Class FixedArray
 * @package Codec\Types
 *
 * FixedArray is Fixed-length array
 * Encode/decode no need to declare length
 *
 */
class FixedArray extends ScaleInstance
{
    public $FixedLength;

    function decode (): array
    {
        $value = [];
        for ($i = 0; $i < $this->FixedLength; $i++) {
            array_push($value, $this->process($this->subType));
        }
        return $value;
    }


    /**
     * @param array $param
     * @return mixed|string|null
     */
    function encode ($param): string
    {
        $value = "";
        if (!is_array($param)) {
            throw new InvalidArgumentException(sprintf('param not array'));
        }
        if (count($param) != $this->FixedLength) {
            throw new InvalidArgumentException(sprintf('param not eq FixedLength'));
        }
        foreach ($param as $index => $item) {
            $value .= $this->createTypeByTypeString($this->subType)->encode($item);
        }
        return $value;
    }
}