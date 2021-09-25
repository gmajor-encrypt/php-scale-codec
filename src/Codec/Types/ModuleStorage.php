<?php


namespace Codec\Types;

use Codec\Generator;

/**
 * Class ModuleStorage
 *
 * @package Codec\Types
 *
 * Storage module
 *
 */
class ModuleStorage extends Struct
{

    /**
     * ModuleStorage constructor.
     *
     * @param Generator $generator
     */
    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "prefix" => "String",
            "items" => "Vec<MetadataModuleStorageEntry>"
        ];
    }


    public function decode (): array
    {
        $value = [];
        $value["prefix"] = $this->process("String");
        $value["items"] = $this->process("Vec<MetadataModuleStorageEntry>");
        return $value;
    }

    public function encode ($param)
    {
        return parent::encode($param);
    }
}
