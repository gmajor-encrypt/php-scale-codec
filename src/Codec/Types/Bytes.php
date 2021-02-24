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
        $length = $this->process("CompactU32", $this->data);
        return sprintf('%s', Utils::bytesToHex($this->nextBytes($length)));
    }


    /**
     * @param $param
     * @return mixed|string|null
     */
    function encode ($param)
    {
        $value = Utils::trimHex($param);
        $instant = $this->createTypeByTypeString("CompactU32");
        $length = $instant->encode(count(Utils::hexToBytes($value)));
        return $length . $value;
    }
}