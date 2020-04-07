<?php


use Codec\Types\ScaleDecoder;
use Codec\Utiles;

class HexBytes extends ScaleDecoder
{

    function decode()
    {
        $length = $this->process("CompactU32", $this->data);
        return sprintf('0x%s', Utiles::bytesToHex($this->nextBytes($length)));
    }
}