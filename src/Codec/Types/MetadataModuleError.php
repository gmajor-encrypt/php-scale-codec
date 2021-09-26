<?php

namespace Codec\Types;

use Codec\Generator;

class MetadataModuleError extends Struct
{

    /**
     * MetadataModuleError constructor
     * https://substrate.dev/docs/en/knowledgebase/runtime/metadata#errors
     * Metadata will pull all the possible runtime errors from decl_error! (FRAME v1) or #[pallet::error] (FRAME v2)
     *
     * Struct
     * {
     *  "name": "string",
     *  "docs": "Vec<string>"
     * }
     *
     *
     * @param Generator $generator
     */
    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "name" => "String",
            "docs" => "Vec<string>",
        ];
    }


    public function decode (): array
    {
        $value = [];
        $value["name"] = $this->process("String");
        $value["docs"] = $this->process("Vec<string>");
        return $value;
    }

    public function encode ($param)
    {
        return parent::encode($param);
    }
}
