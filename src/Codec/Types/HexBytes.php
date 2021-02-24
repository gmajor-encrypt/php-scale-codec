<?php

namespace Codec\Types;

use Codec\Utils;

class HexBytes extends Bytes
{

    function decode ()
    {
        $length = $this->process("CompactU32", $this->data);
        return sprintf('%s', Utils::bytesToHex($this->nextBytes($length)));
    }

    function encode ($param)
    {
        $value = Utils::trimHex($param);
        $instant = $this->createTypeByTypeString("CompactU32");
        $length = $instant->encode(count(Utils::hexToBytes($value)));
        return $length . $value;
    }

}