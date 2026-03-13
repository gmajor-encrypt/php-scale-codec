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

    /**
     * metadataV14 construct function
     *
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "lookup" => "PortableRegistry",
            "pallets" => "Vec<V14Module>",
            "extrinsic" => "ExtrinsicMetadataV14",
            "ty" => "SiLookupTypeId"
        ];
    }

    public function decode(): array
    {
        $metadataRaw = parent::decode();

        // Expand metadataV14

        // PortableRegistry
        $id2Portable = array();
        foreach ($metadataRaw["lookup"] as $item) {
            $id2Portable[$item["id"]] = $item;
        }
        $scaleInfo = new ScaleInfo($this->generator);
        $scaleInfo->regPortableType($id2Portable);
        $this->registeredSiType = $scaleInfo->registeredSiType;
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
                    $calls[] = ["name" => $variant["name"], "args" => $args, "docs" => $variant["docs"], "lookup_index" => $variant["index"]];
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
                    $events[] = ["name" => $variant["name"], "args" => $args, "docs" => $variant["docs"], "lookup_index" => $variant["index"]];
                }
            }

            // call lookup
            foreach ($calls as $call) {
                $lookup = Utils::padLeft(dechex($pallet["index"]), 2) . Utils::padLeft(dechex($call["lookup_index"]), 2);
                $metadataRaw["call_index"][$lookup] = ["module" => ["name" => $pallet["name"]], "call" => $call];
            }
            // event lookup
            foreach ($events as $eventIndex => $event) {
                $lookup = Utils::padLeft(dechex($pallet["index"]), 2) . Utils::padLeft(dechex($event["lookup_index"]), 2);
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
    public function encode($param): \InvalidArgumentException|string
    {
        return parent::encode($param);
    }

}
