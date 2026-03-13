<?php

namespace Codec\Types;

use Codec\Generator;

class MetadataModuleEvent extends Struct
{
    /**
     * MetadataModuleEvent constructor.
     * https://substrate.dev/docs/en/knowledgebase/runtime/metadata#events
     * This metadata snippet is generated from this declaration in frame-system:
     * {
     *  "name":"string",
     *  "args": "Vec<String>",
     *  "docs": "Vec<string>"
     * }
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
