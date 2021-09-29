<?php

namespace Codec\Types;


use InvalidArgumentException;

/**
 * Class Struct
 *
 * @package Codec\Types
 *
 * https://substrate.dev/docs/en/knowledgebase/advanced/codec#data-structures
 * For structures, the values are named, but that is irrelevant for the encoding (names are ignored - only order matters).
 * All containers store elements consecutively. The order of the elements is not fixed, depends on the container, and cannot be relied on at decoding.
 * This implicitly means that decoding some byte-array into a specified structure that enforces an order and then re-encoding it could result in a different byte array than the original that was decoded.
 *
 */
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