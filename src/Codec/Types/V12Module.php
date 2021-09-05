<?php

namespace Codec\Types;

/**
 * Class V12Module
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

class V12Module extends ScaleInstance
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
     * @return array
     *
     */
    public function decode (): array
    {
        $this->name = $this->process("String");

        // decode ModuleStorage
        $storage = $this->process("Option<ModuleStorage>");
        if (!empty($storage)) {
            $this->storage = $storage["items"];
            $this->prefix = $storage["prefix"];
        }

        $calls = $this->process("Option<Vec<MetadataModuleCall>>");
        if (!empty($calls)) {
            $this->calls = $calls;
        }

        $events = $this->process("Option<Vec<MetadataModuleEvent>>");
        if (!empty($events)) {
            $this->events = $events;
        }

        $constants = $this->process("Vec<MetadataModuleConstants>");
        if (!empty($constants)) {
            $this->constants = $constants;
        }

        $errors = $this->process("Vec<MetadataModuleError>");
        if (!empty($errors)) {
            $this->errors = $errors;
        }

        $this->index = $this->process("U8");

        return [
            "name" => $this->name,
            "prefix" => $this->prefix,
            "calls" => $this->calls,
            "events" => $this->events,
            "errors" => $this->errors,
            "constants" => $this->constants,
            "index" => $this->index,
        ];
    }
}


