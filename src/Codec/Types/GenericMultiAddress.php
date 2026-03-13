<?php


namespace Codec\Types;

use Codec\Generator;

/**
 * Class GenericMultiAddress
 * @package Codec\Types
 *
 * MultiAddress type is a wrapper for multiple downstream account formats.
 */
class GenericMultiAddress extends Enum
{
    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            // It's an account ID (pubkey).
            "Id" => "AccountId",
            // It's an account index.
            "Index" => "Compact<u32>",
            // It's some arbitrary raw bytes.
            "Raw" => "Bytes",
            // It's a 32 byte representation.
            "Address32" => "H256",
            // Its a 20 byte representation.like evm address
            "Address20" => "H160",
        ];
    }
}
