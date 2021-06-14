<?php

namespace Codec\Types;

use InvalidArgumentException;

class Set extends ScaleInstance
{
    public function decode(): array
    {
        $setIndex = $this->process("U{$this->BitLength}");
        $value = array();
        if ($setIndex > 0) {
            foreach ($this->valueList as $index => $item) {
                if (($setIndex & intval(2 ** $index)) > 0) {
                    array_push($value, $item);
                }
            }
        }
        return $value;

    }

    public function encode($param)
    {
        $value = 0;
        if (!is_array($param)) {
            throw new InvalidArgumentException(sprintf('param not array'));
        }
        foreach ($this->valueList as $index => $item) {
            if (in_array($item, $param)) {
                $value += 2 ** $index;
            }
        }
        return $this->createTypeByTypeString(sprintf("U{$this->BitLength}"))->encode($value);
    }
}