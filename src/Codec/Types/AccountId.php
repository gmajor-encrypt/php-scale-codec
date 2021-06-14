<?php


namespace Codec\Types;

use Codec\Utils;


class AccountId extends ScaleInstance
{
    public function decode(): string
    {
        return sprintf('%s', Utils::bytesToHex($this->nextBytes(32)));
    }


    public function encode($param)
    {
        return Utils::trimHex($param);
    }
}