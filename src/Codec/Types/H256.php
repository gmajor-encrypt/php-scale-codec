<?php

namespace Codec\Types;

use Codec\Utils;

/**
 * Class H256
 * @package Codec\Types
 *
 * Fixed-length 32 bytes u8
 */
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