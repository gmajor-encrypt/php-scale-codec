<?php

namespace Codec\Types;

use Codec\Utils;

/**
 *
 * Class Bytes
 * @package Codec\Types
 *
 * Similar to Vec<u8>
 */
class Bytes extends ScaleInstance
{
    /**
     * @return mixed|string
     * also return bytes
     */
    public function decode (): string
    {
        $length = gmp_intval($this->process("Compact<u32>", $this->data));
        return Utils::bytesToHex($this->nextBytes($length));
    }


    /**
     * @param $param
     * @return mixed|string|null
     */
    public function encode ($param): ?string
    {
        $value = Utils::trimHex($param);
        $instant = $this->createTypeByTypeString("Compact<u32>");
        $length = $instant->encode(count(Utils::hexToBytes($value)));
        return $length . $value;
    }
}