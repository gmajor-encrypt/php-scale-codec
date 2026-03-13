<?php


namespace Codec\Types;

use Codec\Utils;

/**
 * Class AccountId
 * AccountId A wrapper around an AccountId/PublicKey representation
 * Uint8Array (32 bytes in length)
 *
 * @package Codec\Types
 */
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