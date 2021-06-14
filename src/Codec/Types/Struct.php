<?php

namespace Codec\Types;


use InvalidArgumentException;

class Struct extends ScaleInstance
{

    public function decode(): array
    {
        $result = array();
        foreach ($this->typeStruct as $index => $item) {
            $result[$index] = $this->process($item);
        }
        return $result;
    }

    public function encode($param)
    {
        $value = "";
        foreach ($this->typeStruct as $index => $dataType) {
            if (!array_key_exists($index, $param)) {
                return new InvalidArgumentException(sprintf('%d not in Struct', $index));
            }
            $subInstant = $this->createTypeByTypeString($dataType);
            $value = $value . $subInstant->encode($param[$index]);
        }
        return $value;
    }

}