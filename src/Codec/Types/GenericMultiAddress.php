<?php


namespace Codec\Types;

use Codec\Generator;

class GenericMultiAddress extends Enum
{
    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "Id" => "AccountId",
            "Index" => "Compact<AccountIndex>",
            "Raw" => "Bytes",
            "Address32" => "H256",
            "Address20" => "H160",
        ];
    }
}
