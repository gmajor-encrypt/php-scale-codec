<?php

namespace Codec\Types;

use Codec\Generator;

class MetadataModuleConstants extends Struct
{
    /**
     * MetadataModuleConstants constructor.
     *
     * The metadata will include any module constants
     * https://substrate.dev/docs/en/knowledgebase/runtime/metadata#constants
     * Struct
     * {
     *  "name": "string",
     *  "type": "string",
     *  "value": "bytes",
     *  "docs": "vec<string>"
     * }
     *
     * @param Generator $generator
     */

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
