<?php

namespace Codec\Types;

use Codec\Generator;
use Codec\Utils;

/**
 * Class metadataV14
 * @package Codec\Types
 *
 *  metadata v14 pr  https://github.com/paritytech/substrate/pull/8615
 *  polkadot.js https://github.com/polkadot-js/api/blob/master/packages/types/src/interfaces/metadata/v14.ts
 *  scale.go https://github.com/itering/scale.go/blob/master/types/v14.go
 *  {
 *    "lookup": "PortableRegistry",
 *    "pallets": "Vec<PalletMetadataV14>",
 *    "extrinsic": "ExtrinsicMetadataV14"
 *  }
 */

class metadataV14 extends Struct
{

    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->typeStruct = [
            "lookup" => "PortableRegistry",
            "pallets" => "Vec<V14Module>",
            "extrinsic" => "ExtrinsicMetadataV14"
        ];
    }

    public function decode (): array
    {
        $metadataRaw = parent::decode();

        // Expand metadataV14

        // PortableRegistry
        $id2Portable  = array();
        foreach ($metadataRaw["lookup"] as $item){
            $id2Portable[$item["id"]]= $item;
        }

        $this->regPortableType($id2Portable);

        // pallets
        foreach ($metadataRaw["pallets"] as $palletIndex =>$pallet){

            // storage MetadataV14ModuleStorage
            if(!empty($pallet["storage"])){
                foreach ($pallet["storage"]["items"] as $index =>$item) {

                    // PlainType
                    if($item["type"]["origin"]=="PlainType"){
                        $metadataRaw["pallets"][$palletIndex]["storage"]["items"][$index]["type"]["plain_type"] = "plain_type_id"; //todo
                    } elseif ($item["type"]["origin"]=="Map"){
                        $metadataRaw["pallets"][$palletIndex]["storage"]["items"][$index]["type"]["MapType"]["keys"] = "keys_id"; //todo
                        $metadataRaw["pallets"][$palletIndex]["storage"]["items"][$index]["type"]["MapType"]["values"] = "values_id"; //todo
                    }
                }
            }

            // Call PalletCallMetadataV14
            $calls = array();
            if(!empty($pallet["calls"])){
                $variants = $id2Portable[$pallet["calls"]["type"]];
                foreach ($variants["type"]["def"]["Variant"]["variants"] as $variant){
                    $args = array();
                    foreach ($variant["fields"] as $v){
                        array_push($args,["name"=>$v["name"],"type"=>$v["type"]]);// todo
                    }
                    array_push($calls,["name"=>$variant["name"], "args"=>$args, "docs"=>$variant["docs"]]);
                }
            }

            // Events PalletEventMetadataV14
            $events = array();
            if(!empty($pallet["events"])){
                $variants = $id2Portable[$pallet["events"]["type"]];
                foreach ($variants["type"]["def"]["Variant"]["variants"] as $variant){
                    $args = array();
                    foreach ($variant["fields"] as $v){
                        array_push($args,["name"=>$v["name"],"type"=>$v["type"]]); // todo
                    }
                    array_push($events,["name"=>$variant["name"], "args"=>$args, "docs"=>$variant["docs"]]);
                }
            }

            // call lookup
            foreach ($calls as $callIndex => $call) {
                $lookup = Utils::padLeft(dechex($pallet["index"]), 2) . Utils::padLeft(dechex($callIndex), 2);
                $metadataRaw["pallets"][$palletIndex]["call_index"][$lookup] = ["module" => $pallet["name"], "call" => $call];
            }
            // event lookup
            foreach ($events as $eventIndex => $event) {
                $lookup = Utils::padLeft(dechex($pallet["index"]), 2) . Utils::padLeft(dechex($eventIndex), 2);
                $metadataRaw["pallets"][$palletIndex]["event_index"][$lookup] = ["module" => $pallet["name"], "call" => $event];
            }


            // constants
            foreach ($pallet["constants"] as $index =>$item) {
                $metadataRaw["pallets"][$palletIndex]["constants"][$index]["type_string"] = $item["type"]; // todo
            }

            // errors
            $errors = array();
            if(!empty($pallet["errors"])){
                $variants = $id2Portable[$pallet["errors"]["type"]];
                foreach ($variants["type"]["def"]["Variant"]["variants"] as $variant){
                    array_push($errors,["name"=>$variant["name"], "docs"=>$variant["docs"]]);
                }
            }
            $metadataRaw["pallets"]["$palletIndex"]["errors_value"] = $errors;
        }
        return $metadataRaw;
    }

    public function encode ($param)
    {
        return parent::encode($param);
    }

    /**
     *
     * todo
     * @param array $id2Portable
     */
    private function regPortableType(array $id2Portable){

    }
}


