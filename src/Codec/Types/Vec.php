<?php

namespace Codec\Types;

use Codec\Utils;
use InvalidArgumentException;

class Vec extends ScaleInstance
{

    public function decode (): array
    {
        $VecLength = $this->process("Compact<u32>");
        $value = [];
        $subType = $this->subType;
        for ($i = 0; $i < $VecLength; $i++) {
            array_push($value, $this->process($subType));
        }
        return $value;
    }


    public function encode ($param)
    {
        if (!is_array($param)) {
            return new InvalidArgumentException(sprintf('%v not array', $param));
        }

        $instant = $this->createTypeByTypeString("Compact<u32>");
        $length = $instant->encode(count($param));

        $value = $length;
        $subType = $this->subType;
        foreach ($param as $index => $item) {
            $subInstant = $this->createTypeByTypeString($subType);
            $value = $value . $subInstant->encode($item);
        }
        return $value;
    }
}