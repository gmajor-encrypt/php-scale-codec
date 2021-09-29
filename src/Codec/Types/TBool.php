<?php

namespace Codec\Types;

/**
 * Class TBool
 *
 * @package Codec\Types
 *
 * Boolean values are encoded using the least significant bit of a single byte.
 * https://substrate.dev/docs/en/knowledgebase/advanced/codec#boolean
 *
 *
 *
 */
class TBool extends ScaleInstance
{

    /**
     * TBool is scale bool type
     *
     * @return bool
     */
    public function decode(): bool
    {
        // check next first bytes is 0
        return $this->nextBool();
    }

    /**
     * @param mixed $param
     * @return string
     */
    public function encode($param): string
    {
        return $param == true ? "01" : "00";
    }
}