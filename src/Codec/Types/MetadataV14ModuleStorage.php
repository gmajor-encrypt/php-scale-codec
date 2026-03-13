<?php


namespace Codec\Types;

use Codec\Generator;

/**
 * Class MetadataV14ModuleStorage
 *
 * @package Codec\Types
 *
 * Storage module
 *
 * Every storage item that is defined in a pallet will have a corresponding metadata entry.
 * Struct
 * {
 *   "prefix": "string"
 *   "items" : "vec<MetadataV14ModuleStorageEntry>"
 * }
 */
class MetadataV14ModuleStorage extends Struct
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
            "items" => "Vec<MetadataV14ModuleStorageEntry>"
        ];
    }


    public function decode (): array
    {
        return parent::decode();
    }

    public function encode ($param)
    {
        return parent::encode($param);
    }
}
