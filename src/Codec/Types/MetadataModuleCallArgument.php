<?php

namespace Codec\Types;


use Codec\Generator;

class MetadataModuleCallArgument extends Struct
{

    /**
     * MetadataModuleCallArgument constructor.
     *
     * MetadataModuleCallArgument is  MetadataModuleCall args
     *
     * Struct
     * {
     *   "name": "string",
     *   "type": "string"
     *
     *
     * @param Generator $generator
     */
    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "name" => "String",
            "type" => "String"
        ];
    }

    public function decode (): array
    {
        $value = [];
        $value["name"] = $this->process("String");
        $value["type"] = $this->process("String");
        return $value;
    }

    public function encode ($param)
    {
        return parent::encode($param);
    }
}
