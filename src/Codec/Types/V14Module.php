<?php

namespace Codec\Types;

use Codec\Generator;

/**
 * Class V14Module
 *
 * @package Codec\Types
 *
 *
 */
class V14Module extends Struct
{
    public $storage;

    public $calls;

    public $events;

    public $constants;

    public $errors;

    public $index;

    /**
     * metadata v14 decode
     * struct include storage, calls, events, constants, errors, index
     * {
     *   "storage": "Option<ModuleStorage>",
     *   "calls": "Option<PalletCallMetadataV14>",
     *   "events": "Option<PalletEventMetadataV14>",
     *   "constants": "Vec<PalletConstantMetadataV14>",
     *   "errors": "Vec<PalletErrorMetadataV14>",
     *   "index": "u8"
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
            "storage" => "Option<MetadataV14ModuleStorage>",
            "calls" => "Option<PalletCallMetadataV14>",
            "events" => "Option<PalletEventMetadataV14>",
            "constants" => "Vec<PalletConstantMetadataV14>",
            "errors" => "Option<PalletErrorMetadataV14>",
            "index" => "U8"
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


