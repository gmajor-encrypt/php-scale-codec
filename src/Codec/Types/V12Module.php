<?php

namespace Codec\Types;

use Codec\Generator;

/**
 * Class V12Module
 *
 * @package Codec\Types
 *
 * example struct
 *  {
 *  "magicNumber": 1635018093,
 *  "metadata": {
 *    "V12": {
 *        "modules": [
 *        {
 *            // ...
 *        },
 *        {
 *            //...
 *        }
 *      ],
 *      "extrinsic": {
 *        "version": 4,
 *        "signedExtensions": [
 *            "CheckSpecVersion",
 *            "CheckTxVersion",
 *            "CheckGenesis",
 *            "CheckMortality",
 *            "CheckNonce",
 *            "CheckWeight",
 *            "ChargeTransactionPayment"
 *            ]
 *      }
 *    }
 *  }
 *}
 *
 */
class V12Module extends Struct
{
    public $name;

    public $prefix;

    public $storage;

    public $calls;

    public $events;

    public $constants;

    public $errors;

    public $index;

    /**
     * metadata v12 decode
     * struct include storage, calls, events, constants, errors, index
     * {
     *   "storage": "Option<ModuleStorage>",
     *   "calls": "Option<Vec<MetadataModuleCall>>",
     *   "events": "Option<Vec<MetadataModuleEvent>>",
     *   "constants": "Vec<MetadataModuleConstants>",
     *   "errors": "Vec<MetadataModuleError>",
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
            "storage" => "Option<ModuleStorage>",
            "calls" => "Option<Vec<MetadataModuleCall>>",
            "events" => "Option<Vec<MetadataModuleEvent>>",
            "constants" => "Vec<MetadataModuleConstants>",
            "errors" => "Vec<MetadataModuleError>",
            "index" => "U8"
        ];
    }


    public function decode (): array
    {
        $this->name = $this->process("String");

        // decode ModuleStorage
        $storage = $this->process("Option<ModuleStorage>");
        $this->storage = !empty($storage) ? $storage : null;

        $calls = $this->process("Option<Vec<MetadataModuleCall>>");
        $this->calls = !empty($calls) ? $calls : null;

        $events = $this->process("Option<Vec<MetadataModuleEvent>>");
        $this->events = !empty($events) ? $events : null;


        $constants = $this->process("Vec<MetadataModuleConstants>");
        $this->constants = !empty($constants) ? $constants : [];

        $errors = $this->process("Vec<MetadataModuleError>");
        $this->errors = !empty($errors) ? $errors : [];

        $this->index = $this->process("U8");

        return [
            "name" => $this->name,
            "storage" => $this->storage,
            "calls" => $this->calls,
            "events" => $this->events,
            "errors" => $this->errors,
            "constants" => $this->constants,
            "index" => $this->index,
        ];
    }

    public function encode ($param)
    {
        return parent::encode($param);
    }
}


