<?php

namespace Codec\Types;

use Codec\Generator;

/**
 * Class MetadataModuleCall
 *  https://substrate.dev/docs/en/knowledgebase/runtime/metadata#dispatchable-calls
 *
 *  Metadata for dispatchable calls includes information about the runtime calls and are defined by the decl_module! (FRAME v1) or #[pallet] (FRAME v2) macros
 * Struct
 * {
 *   "name": "string"
 *   "arg" : "Vec<MetadataModuleCallArgument>"
 *   "docs" "Vec<string>"
 * }
 *
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
