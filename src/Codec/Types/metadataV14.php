<?php

namespace Codec\Types;

use Codec\Base;
use Codec\Generator;
use Codec\Utils;

/**
 * Class metadataV14
 *
 * @package Codec\Types
 *
 *  metadata v14 pr  https://github.com/paritytech/substrate/pull/8615
 *  polkadot.js https://github.com/polkadot-js/api/blob/master/packages/types/src/interfaces/metadata/v14.ts
 *  scale.go https://github.com/itering/scale.go/blob/master/types/v14.go
 *   https://github.com/paritytech/frame-metadata/blob/717f6341c1f4d8dc1f15d49071ee73dd064d2e67/frame-metadata/src/v14.rs
 *  {
 *    "lookup": "PortableRegistry",
 *     // Metadata of all the pallets.
 *    "pallets": "Vec<PalletMetadataV14>",
 *     // Metadata of the extrinsic.
 *    "extrinsic": "ExtrinsicMetadataV14",
 *     // The type of the `Runtime`.
 *     ty" => "SiLookupTypeId"
 *  }
 */
class metadataV14 extends Struct
{
    /**
     * @var $registeredSiType array
     */
    protected array $registeredSiType;

    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "lookup" => "PortableRegistry",
            "pallets" => "Vec<V14Module>",
            "extrinsic" => "ExtrinsicMetadataV14",
            "ty" => "SiLookupTypeId"
        ];
    }

    public function decode (): array
    {
        $metadataRaw = parent::decode();

        // Expand metadataV14

        // PortableRegistry
        $id2Portable = array();
        foreach ($metadataRaw["lookup"] as $item) {
            $id2Portable[$item["id"]] = $item;
        }

        $this->regPortableType($id2Portable);

        // pallets
        foreach ($metadataRaw["pallets"] as $palletIndex => $pallet) {

            // storage MetadataV14ModuleStorage
            if (!empty($pallet["storage"])) {
                foreach ($pallet["storage"]["items"] as $index => $item) {

                    // PlainType
                    if ($item["type"]["origin"] == "PlainType") {
                        $metadataRaw["pallets"][$palletIndex]["storage"]["items"][$index]["type"]["plain_type"] = $this->registeredSiType[$item["type"]["plain_type_id"]];
                    } elseif ($item["type"]["origin"] == "Map") {
                        $metadataRaw["pallets"][$palletIndex]["storage"]["items"][$index]["type"]["MapType"]["keys"] = $this->registeredSiType[$item["type"]["MapType"]["keys_id"]];
                        $metadataRaw["pallets"][$palletIndex]["storage"]["items"][$index]["type"]["MapType"]["values"] = $this->registeredSiType[$item["type"]["MapType"]["values_id"]];
                    }
                }
            }

            // Call PalletCallMetadataV14
            $calls = array();
            if (!empty($pallet["calls"])) {
                $variants = $id2Portable[$pallet["calls"]["type"]];
                foreach ($variants["type"]["def"]["Variant"]["variants"] as $variant) {
                    $args = array();
                    foreach ($variant["fields"] as $v) {
                        $args[] = ["name" => $v["name"], "type" => $this->registeredSiType[$v["type"]]];
                    }
                    $calls[] = ["name" => $variant["name"], "args" => $args, "docs" => $variant["docs"]];
                }
            }

            // Events PalletEventMetadataV14
            $events = array();
            if (!empty($pallet["events"])) {
                $variants = $id2Portable[$pallet["events"]["type"]];
                foreach ($variants["type"]["def"]["Variant"]["variants"] as $variant) {
                    $args = array();
                    foreach ($variant["fields"] as $v) {
                        $args[] = $this->registeredSiType[$v["type"]];
                    }
                    $events[] = ["name" => $variant["name"], "args" => $args, "docs" => $variant["docs"]];
                }
            }

            // call lookup
            foreach ($calls as $callIndex => $call) {
                $lookup = Utils::padLeft(dechex($pallet["index"]), 2) . Utils::padLeft(dechex($callIndex), 2);
                $metadataRaw["call_index"][$lookup] = ["module" => ["name" => $pallet["name"]], "call" => $call];
            }
            // event lookup
            foreach ($events as $eventIndex => $event) {
                $lookup = Utils::padLeft(dechex($pallet["index"]), 2) . Utils::padLeft(dechex($eventIndex), 2);
                $metadataRaw["event_index"][$lookup] = ["module" => ["name" => $pallet["name"]], "call" => $event];
            }


            // constants
            foreach ($pallet["constants"] as $index => $item) {
                $metadataRaw["pallets"][$palletIndex]["constants"][$index]["type_string"] = $this->registeredSiType[$item["type"]];
            }

            // errors
            $errors = array();
            if (!empty($pallet["errors"])) {
                $variants = $id2Portable[$pallet["errors"]["type"]];
                foreach ($variants["type"]["def"]["Variant"]["variants"] as $variant) {
                    $errors[] = ["name" => $variant["name"], "docs" => $variant["docs"]];
                }
            }
            $metadataRaw["pallets"]["$palletIndex"]["errors_value"] = $errors;
        }
        return $metadataRaw;
    }

    /**
     * @param $param
     * @return \InvalidArgumentException|string
     */
    public function encode ($param): \InvalidArgumentException|string
    {
        return parent::encode($param);
    }

    /**
     *
     *
     * @param array $id2Portable
     */
    private function regPortableType (array $id2Portable)
    {
        foreach ($id2Portable as $id => $item) {
            if (array_key_exists("Primitive", $item["type"]["def"])) {
                $this->registeredSiType[$id] = $item["type"]["def"]["Primitive"];
            }
        }
        foreach ($id2Portable as $id => $item) {
            if (count($item["type"]["path"]) > 1 && current($item["type"]["path"]) == "primitive_types") {
                $this->registeredSiType[$id] = end($item["type"]["path"]);
            }
        }

        foreach ($id2Portable as $id => $item) {
            if (count($item["type"]["path"]) > 1 && current($item["type"]["path"]) == "sp_core") {
                $this->dealOnePortableType($id, $item, $id2Portable);
            }
        }

        foreach ($id2Portable as $id => $item) {
            if (!array_key_exists($id, $this->registeredSiType)) {
                $this->dealOnePortableType($id, $item, $id2Portable);
            }
        }
    }

    /**
     * dealOnePortableType
     *
     * @param int $id
     * @param array $one
     * @param array $id2Portable
     * @return String
     */
    private function dealOnePortableType (int $id, array $one, array $id2Portable): string
    {


        // Composite, struct
        $one = $one["type"];
        if (array_key_exists("Composite", $one["def"])) {
            return self::expandComposite($id, $one, $id2Portable);
        }
        // Array, Fixed
        if (array_key_exists("Array", $one["def"])) {
            return self::expandArray($id, $one, $id2Portable);
        }

        // Sequence, vendor
        if (array_key_exists("Sequence", $one["def"])) {
            return self::expandSequence($id, $one, $id2Portable);
        }

        // Tuple
        if (array_key_exists("Tuple", $one["def"])) {
            return self::expandTuple($id, $one, $id2Portable);
        }

        // Compact
        if (array_key_exists("Compact", $one["def"])) {
            return self::expandCompact($id, $one, $id2Portable);
        }
        // BitSequence
        if (array_key_exists("BitSequence", $one["def"])) {
            $this->registeredSiType[$id] = "BitVec";
            return $this->registeredSiType[$id];
        }
        // Variant
        if (array_key_exists("Variant", $one["def"])) {
            $VariantType = $one["path"][0];
            switch ($VariantType) {
                // option
                case "Option":
                    return self::expandOption($id, $one, $id2Portable);
                // Result
                case "Result":
                    return self::expandResult($id, $one, $id2Portable);
            }
            // pallet Call, Event, Error, metadata deal
            if (count($one["path"]) >= 2) {
                if (in_array(end($one["path"]), ["Call", "Event"])) {
                    $this->registeredSiType[$id] = "Call";
                    return "Call";
                }
                if (end($one["path"]) == "Call" && $one["path"][count($one["path"]) - 2] == "pallet") {
                    $this->registeredSiType[$id] = "Call";
                    return "Call";
                }
                if (end($one["path"]) == "Instruction") { // todo xcm
                    $this->registeredSiType[$id] = "Call";
                    return "Call";
                }
            }
            // Enum
            return self::expandEnum($id, $one, $id2Portable);
        }
        $this->registeredSiType[$id] = "NULL";
        return "NULL";
    }

    /**
     * genPathName
     * generate type name by struct
     *
     * @param array $path
     * @return string
     */
    private function genPathName (array $path): string
    {
        return join(":", $path);
    }

    /**
     * expandComposite
     *
     * @param int $id
     * @param array $one
     * @param array $id2Portable
     * @return string
     */
    private function expandComposite (int $id, array $one, array $id2Portable): string
    {
        $typeString = self::genPathName($one["path"]);

        if (count($one["def"]["Composite"]["fields"]) == 0) {
            $this->registeredSiType[$id] = "NULL";
            return "NULL";
        }

        if (count($one["def"]["Composite"]["fields"]) == 1) {
            $siType = intval($one["def"]["Composite"]["fields"][0]["type"]);
            // check subType
            $subType = array_key_exists($siType, $this->registeredSiType) ? $this->registeredSiType[$siType] :
                $this->dealOnePortableType($siType, $id2Portable[$siType], $id2Portable);
            Base::regCustom($this->generator, [$typeString => $subType]);
        } else {
            $tempStruct = [];
            foreach ($one["def"]["Composite"]["fields"] as $field) {
                $tempStruct[$field["name"]] = array_key_exists($field["type"], $this->registeredSiType) ? $this->registeredSiType[$field["type"]] :
                    $this->dealOnePortableType($field["type"], $id2Portable[$field["type"]], $id2Portable);
            }
            $instant = clone $this->generator->getRegistry("struct");
            $instant->typeStruct = $tempStruct;
            $this->generator->addScaleType($typeString, $instant);
        }
        $this->registeredSiType[$id] = $typeString;
        return $typeString;
    }


    /**
     * expandArray
     *
     * @param int $id
     * @param array $one
     * @param array $id2Portable
     * @return string
     */
    private function expandArray (int $id, array $one, array $id2Portable): string
    {
        $siType = intval($one["def"]["Array"]["type"]);

        $this->registeredSiType[$id] = sprintf("[%s; %d]",
            array_key_exists($siType, $this->registeredSiType) ?
                $this->registeredSiType[$siType] : $this->dealOnePortableType($siType, $id2Portable[$siType], $id2Portable),
            $one["def"]["Array"]["len"]);

        return $this->registeredSiType[$id];
    }

    /**
     * expandSequence
     *
     * @param int $id
     * @param array $one
     * @param array $id2Portable
     * @return string
     */
    private function expandSequence (int $id, array $one, array $id2Portable): string
    {
        $siType = intval($one["def"]["Sequence"]["type"]);

        $this->registeredSiType[$id] = array_key_exists($siType, $this->registeredSiType) ?
            sprintf("Vec<%s>", $this->registeredSiType[$siType]) :
            sprintf("Vec<%s>", $this->dealOnePortableType($siType, $id2Portable[$siType], $id2Portable));
        return $this->registeredSiType[$id];
    }


    /**
     * expandTuple
     *
     * @param int $id
     * @param array $one
     * @param array $id2Portable
     * @return string
     */
    private function expandTuple (int $id, array $one, array $id2Portable): string
    {
        if (count($one["def"]["Tuple"]) == 0) {
            $this->registeredSiType[$id] = "NULL";
            return "NULL";
        }
        $tuple1 = intval($one["def"]["Tuple"][0]);
        $tuple2 = intval($one["def"]["Tuple"][1]);
        $tuple1Type = array_key_exists($tuple1, $this->registeredSiType) ? $this->registeredSiType[$tuple1] :
            $this->dealOnePortableType($tuple1, $id2Portable[$tuple1], $id2Portable);
        $tuple2Type = array_key_exists($tuple2, $this->registeredSiType) ? $this->registeredSiType[$tuple2] :
            $this->dealOnePortableType($tuple2, $id2Portable[$tuple2], $id2Portable);
        // combine (a,b) Tuple
        $this->registeredSiType[$id] = sprintf("(%s,%s)", $tuple1Type, $tuple2Type);
        return $this->registeredSiType[$id];
    }

    /**
     * expandCompact
     *
     * @param int $id
     * @param array $one
     * @param array $id2Portable
     * @return string
     */
    private function expandCompact (int $id, array $one, array $id2Portable): string
    {
        $siType = intval($one["def"]["Compact"]["type"]);
        $this->registeredSiType[$id] = array_key_exists($siType, $this->registeredSiType) ?
            sprintf("Compact<%s>", $this->registeredSiType[$siType]) :
            sprintf("Compact<%s>", $this->dealOnePortableType($siType, $id2Portable[$siType], $id2Portable));
        return $this->registeredSiType[$id];
    }

    /**
     * expandOption
     *
     * @param int $id
     * @param array $one
     * @param array $id2Portable
     * @return string
     */
    private function expandOption (int $id, array $one, array $id2Portable): string
    {
        $siType = intval($one["params"][0]["type"]);
        $this->registeredSiType[$id] = array_key_exists($siType, $this->registeredSiType) ?
            sprintf("Option<%s>", $this->registeredSiType[$siType]) :
            sprintf("Option<%s>", $this->dealOnePortableType($siType, $id2Portable[$siType], $id2Portable));
        return $this->registeredSiType[$id];
    }

    /**
     * expandResult
     *
     * @param int $id
     * @param array $one
     * @param array $id2Portable
     * @return string
     */
    private function expandResult (int $id, array $one, array $id2Portable): string
    {
        $ResultOk = intval($one["params"][0]["type"]);
        $ResultErr = intval($one["params"][1]["type"]);
        $okType = array_key_exists($ResultOk, $this->registeredSiType) ?
            $this->registeredSiType[$ResultOk] :
            $this->dealOnePortableType($ResultOk, $id2Portable[$ResultOk], $id2Portable);
        $errType = array_key_exists($ResultErr, $this->registeredSiType) ?
            $this->registeredSiType[$ResultErr] :
            $this->dealOnePortableType($ResultErr, $id2Portable[$ResultErr], $id2Portable);
        // combine (a,b) Tuple
        $this->registeredSiType[$id] = sprintf("Result<%s,%s>", $okType, $errType);
        return $this->registeredSiType[$id];
    }

    /**
     * expandEnum
     *
     * @param int $id
     * @param array $one
     * @param array $id2Portable
     * @return string
     */
    private function expandEnum (int $id, array $one, array $id2Portable): string
    {
        $enumValueList = [];
        // sort by enum index
        $variants = $one["def"]["Variant"]["variants"];
        usort($variants, function ($pre, $next) {
            return ($pre["index"] < $next["index"]) ? -1 : 1;
        });

        foreach ($variants as $index => $variant) {
            $name = $variant["name"];
            $enumIndex = $variant["index"];

            // fill empty element
            $interval = $enumIndex;
            if ($index > 0) {
                $interval = $enumIndex - $variants[$index - 1]["index"] - 1;
            }
            while ($interval > 0) {
                $enumValueList[sprintf("empty%d", $interval)] = "NULL";
                $interval--;
            }

            switch (count($variant["fields"])) {
                case 0:
                    $enumValueList[$name] = "NULL";
                    break;
                case 1:
                    $siType = $variant["fields"][0]["type"];
                    $enumValueList[$name] = array_key_exists($siType, $this->registeredSiType) ? $this->registeredSiType[$siType] :
                        self::genPathName($id2Portable[$siType]["type"]["path"]);
                    break;

                default:
                    // field count> 1, enum one element is struct
                    // If there is no name the fields are a tuple
                    if ($variant["fields"][0]["name"] === null) {
                        $typeMapping = "";
                        foreach ($variant["fields"] as $field) {
                            $siType = $field["type"];

                            $typeMapping !== "" && $typeMapping .= ", ";
                            $typeMapping .= array_key_exists($siType, $this->registeredSiType) ? $this->registeredSiType[$siType] :
                                self::genPathName($id2Portable[$siType]["type"]["path"]);
                        }
                        $enumValueList[$name] = sprintf("(%s)", $typeMapping);
                        break;
                    }

                    $typeMapping = [];
                    foreach ($variant["fields"] as $field) {
                        $valueName = $field["name"];
                        $siType = $field["type"];
                        $typeMapping[$valueName] = array_key_exists($siType, $this->registeredSiType) ? $this->registeredSiType[$siType] : self::genPathName($id2Portable[$siType]["type"]["path"]);
                    }
                    $enumValueList[$name] = json_encode($typeMapping);
                    break;
            }
        }


        $instant = clone $this->generator->getRegistry("enum");
        $instant->typeStruct = $enumValueList;
        $typeString = self::genPathName($one["path"]);
        $this->generator->addScaleType($typeString, $instant);
        $this->registeredSiType[$id] = $typeString;
        return $typeString;
    }
}
