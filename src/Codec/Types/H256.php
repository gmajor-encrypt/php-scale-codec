<?php

namespace Codec\Types;

use Codec\Utils;

class H256 extends Bytes
{

    function decode ()
    {
        return sprintf('%s', Utils::bytesToHex($this->nextBytes(32)));
    }

    function encode ($param)
    {
        $value = Utils::trimHex($param);
        return $value;
    }

}