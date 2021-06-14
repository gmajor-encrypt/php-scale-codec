<?php

namespace Codec\Types;

use Codec\Utils;

class H256 extends Bytes
{

    public function decode(): string
    {
        return sprintf('%s', Utils::bytesToHex($this->nextBytes(32)));
    }

    public function encode($param): string
    {
        return Utils::trimHex($param);
    }

}