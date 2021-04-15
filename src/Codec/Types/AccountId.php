<?php


namespace Codec\Types;

use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use Codec\Utils;


class AccountId extends ScaleInstance
{
    public function decode ()
    {
        return sprintf('%s', Utils::bytesToHex($this->nextBytes(32)));
    }


    function encode ($param)
    {
        $value = Utils::trimHex($param);
        return $value;
    }
}