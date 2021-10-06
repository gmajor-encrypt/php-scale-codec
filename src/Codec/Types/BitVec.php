<?php


namespace Codec\Types;

use Codec\Utils;

/**
 * Class BitVec
 *
 * A BitVec that represents an array of bits
 * @package Codec\Types
 */
class BitVec extends ScaleInstance
{
    public function decode(): string
    {
        $length = $this->process("Compact<u32>");
        return sprintf('%s', Utils::bytesToHex($this->nextBytes(ceil($length/8))));
    }


    public function encode($param)
    {
        $instant = $this->createTypeByTypeString("Compact<u32>");
        $length = $instant->encode(count(Utils::hexToBytes($param))*8);
        return $length.$param;
    }
}