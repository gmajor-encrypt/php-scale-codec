<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;

class HexBytes extends ScaleDecoder
{

    function decode()
    {
        $length = $this->process("CompactU32", $this->data);
        return sprintf('0x%s', Utils::bytesToHex($this->nextBytes($length)));
    }
}