<?php

namespace Codec\Types;


/**
 * Class Tuples
 *
 * @package Codec\Types
 *
 * A fixed-size series of values, each with a possibly different but predetermined and fixed type. This is simply the concatenation of each encoded value.
 * https://substrate.dev/docs/en/knowledgebase/advanced/codec#tuples
 */
class Tuples extends ScaleInstance
{

    public function decode (): array
    {
        $result = array();
        foreach ($this->typeStruct as $item) {
            $result[] = $this->process($item);
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