<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;

class Bytes extends ScaleDecoder
{
    /**
     * @return mixed|string
     * also return bytes
     */
    function decode ()
    {
        $length = $this->process("Compact<u32>", $this->data);
        return sprintf('%s', Utils::bytesToHex($this->nextBytes($length)));
    }


    /**
     * @param $param
     * @return mixed|string|null
     */
    function encode ($param)
    {
        $value = Utils::trimHex($param);
        $instant = $this->createTypeByTypeString("Compact");
        $length = $instant->encode(count(Utils::hexToBytes($value)));
        return $length . $value;
    }
}