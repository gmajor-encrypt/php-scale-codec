<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;

class HexBytes extends ScaleDecoder
{

    function decode ()
    {
        $length = $this->process("CompactU32", $this->data);
        return sprintf('0x%s', Utils::bytesToHex($this->nextBytes($length)));
    }

    function encode ($param)
    {
        $value = Utils::trimHex($param);
        $instant = $this->createTypeByTypeString("CompactU32");
        $length = $instant->encode(strlen($param));
        return $length . $value;
    }

}