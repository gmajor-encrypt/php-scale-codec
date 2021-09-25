<?php

namespace Codec\Types;

use Codec\Generator;

/**
 * Class MetadataModuleCall
 *
 * @package Codec\Types
 */
class MetadataModuleCall extends Struct
{

    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "name" => "String",
            "args" => "Vec<MetadataModuleCallArgument>",
            "docs" => "Vec<string>"
        ];
    }


    public function decode (): array
    {
        $value = [];
        $value["name"] = $this->process("String");
        $value["args"] = $this->process("Vec<MetadataModuleCallArgument>");
        $value["docs"] = $this->process("Vec<string>");
        return $value;
    }


    public function encode ($param)
    {
        return parent::encode($param);
    }
}
