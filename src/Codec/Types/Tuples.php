<?php

namespace Codec\Types;


class Tuples extends ScaleInstance
{

    public function decode (): array
    {
        $result = array();
        foreach ($this->typeStruct as $index => $item) {
            array_push($result, $this->process($item));
        }
        return $result;
    }

    public function encode ($param): string
    {
        $value = "";
        foreach ($this->typeStruct as $index => $dataType) {
            $subInstant = $this->createTypeByTypeString($dataType);
            $value = $value . $subInstant->encode($param[$index]);
        }
        return $value;
    }

}