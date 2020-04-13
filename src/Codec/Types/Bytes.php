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
    function decode()
    {
        $value = $this->nextBytes($this->process("CompactU32", $this->data));
        return Utils::bytesToHex($value);
    }
}