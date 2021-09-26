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
 *  Every storage item that is defined in a pallet will have a corresponding metadata entry.
 * Struct
 * {
 *   "prefix": "string"
 *   "items" : "vec<MetadataModuleStorageEntry>"
 * }
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
