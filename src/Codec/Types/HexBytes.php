<?php

namespace Codec\Types;

use Codec\Utils;

class HexBytes extends Bytes
{

    function decode ()
    {
        $length = $this->process("Compact<u32>", $this->data);
        return sprintf('%s', Utils::bytesToHex($this->nextBytes($length)));
    }

    function encode ($param)
    {
        $value = Utils::trimHex($param);
        $instant = $this->createTypeByTypeString("Compact");
        $length = $instant->encode(count(Utils::hexToBytes($value)));
        return $length . $value;
    }

}