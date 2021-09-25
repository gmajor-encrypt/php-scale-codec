<?php

namespace Codec\Types;

use Codec\Generator;

class MetadataModuleConstants extends Struct
{

    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "name" => "String",
            "type" => "String",
            "value" => "Bytes",
            "docs" => "vec<string>",
        ];
    }

    public function decode (): array
    {
        $value = [];
        $value["name"] = $this->process("String");
        $value["type"] = $this->process("String");
        $value["value"] = $this->process("Bytes");
        $value["docs"] = $this->process("vec<string>");
        return $value;
    }

    public function encode ($param)
    {
        return parent::encode($param);
    }
}
