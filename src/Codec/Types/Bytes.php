<?php

namespace Codec\Types;

use Codec\Utils;

class Bytes extends ScaleInstance
{
    /**
     * @return mixed|string
     * also return bytes
     */
    public function decode(): string
    {
        $length = gmp_intval($this->process("Compact", $this->data));
        return sprintf('%s', Utils::bytesToHex($this->nextBytes($length)));
    }


    /**
     * @param $param
     * @return mixed|string|null
     */
    public function encode($param): ?string
    {
        $value = Utils::trimHex($param);
        $instant = $this->createTypeByTypeString("Compact");
        $length = $instant->encode(count(Utils::hexToBytes($value)));
        return $length . $value;
    }
}