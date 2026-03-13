<?php

namespace Codec\Types;

use InvalidArgumentException;

/**
 * Class Vec
 *
 * @package Codec\Types
 *
 * A collection of same-typed values is encoded, prefixed with a compact encoding of the number of items, followed by each item's encoding concatenated in turn
 *
 * https://substrate.dev/docs/en/knowledgebase/advanced/codec#vectors-lists-series-sets
 */
class Vec extends ScaleInstance
{


    public function decode (): array
    {
        $VecLength = $this->process("Compact<u32>");
        $value = [];
        $subType = $this->subType;
        for ($i = 0; $i < $VecLength; $i++) {
            $value[] = $this->process($subType);
        }
        return $value;
    }


    public function encode ($param): InvalidArgumentException|string|null
    {
        if (!is_array($param)) {
            return new InvalidArgumentException(sprintf('%v not array', $param));
        }

        $instant = $this->createTypeByTypeString("Compact<u32>");
        $length = $instant->encode(count($param));

        $value = $length;
        $subType = $this->subType;
        foreach ($param as $item) {
            $subInstant = $this->createTypeByTypeString($subType);
            $value = $value . $subInstant->encode($item);
        }
        return $value;
    }
}