<?php

namespace Codec\Types;

use Codec\Generator;

class MetadataModuleEvent extends Struct
{
    /**
     * MetadataModuleEvent constructor.
     *
     * @param Generator $generator
     */
    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "name" => "String",
            "args" => "Vec<String>",
            "docs" => "Vec<string>"
        ];
    }

    public function decode (): array
    {
        $value = [];
        $value["name"] = $this->process("String");
        $value["args"] = $this->process("Vec<String>");
        $value["docs"] = $this->process("Vec<string>");
        return $value;
    }

    public function encode ($param)
    {
        return parent::encode($param);
    }
}
