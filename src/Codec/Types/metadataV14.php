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
    /**
     * @var $registeredSiType array
     */
    protected $registeredSiType;

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
     *
     * @param array $id2Portable
     */
    private function regPortableType(array $id2Portable){
        foreach ($id2Portable as $id=>$item){
            if(array_key_exists("Primitive",$item["type"]["def"])){
                $this->registeredSiType[$id] = $item["type"]["def"]["Primitive"];
            }
        }
        foreach ($id2Portable as $id=>$item){
            if(!array_key_exists($id,$this->registeredSiType)){
                $this->dealOnePortableType($id,$item,$id2Portable);
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
    private function dealOnePortableType(int $id, array $one,array $id2Portable) :String{
        // Composite, struct
        $one = $one["type"];
        if(array_key_exists("Composite",$one["def"])){
            if(count($one["def"]["Composite"]["fields"])==1){
                $subType = intval($one["def"]["Composite"]["fields"][0]["type"]);
                // check subType
                $this->registeredSiType[$id] = array_key_exists($subType,$this->registeredSiType) ? $this->registeredSiType[$subType]:
                    $this->dealOnePortableType($subType,$id2Portable[$subType],$id2Portable);
                return $this->registeredSiType[$id];
            }else{
                $tempStruct = [];
                foreach ($one["def"]["Composite"]["fields"] as $field){
                    $tempStruct[$field["name"]] = array_key_exists($field["type"],$this->registeredSiType) ? $this->registeredSiType[$field["type"]]:
                        $this->dealOnePortableType($field["type"],$id2Portable[$field["type"]],$id2Portable);
                }
                $instant = clone $this->generator->getRegistry("struct");
                $instant->typeStruct = $tempStruct;
                $typeString = end($one["path"]);
                $this->generator->addScaleType($typeString, $instant);
                return $typeString;
            }
        }
        // Array, Fixed
        if(array_key_exists("Array",$one["def"])){
            $subType = intval($one["def"]["Array"]["type"]);
            $this->registeredSiType[$id] = array_key_exists($subType,$this->registeredSiType) ? sprintf("[%s; %d]",$this->registeredSiType[$subType],$one["def"]["Array"]["len"]):
                sprintf("[%s; %d]",$this->dealOnePortableType($subType,$id2Portable[$subType],$id2Portable),$one["def"]["Array"]["len"]);
            return $this->registeredSiType[$id];
        }

        // Sequence, vendor
        if(array_key_exists("Sequence",$one["def"])){
            $subType = intval($one["def"]["Sequence"]["type"]);
            $this->registeredSiType[$id] = array_key_exists($subType,$this->registeredSiType) ? sprintf("Vec<%s>",$this->registeredSiType[$subType]):
                sprintf("Vec<%s>",$this->dealOnePortableType($subType,$id2Portable[$subType],$id2Portable));
            return $this->registeredSiType[$id];
        }

        // Tuple
        if(array_key_exists("Tuple",$one["def"])){
            if(count($one["def"]["Tuple"])==0){
                $this->registeredSiType[$id] = "NULL";
                return "NULL";
            }
            $tuple1 = intval($one["def"]["Tuple"][0]);
            $tuple2 = intval($one["def"]["Tuple"][1]);
            $tuple1Type = array_key_exists($tuple1,$this->registeredSiType) ? $this->registeredSiType[$tuple1]:
                $this->dealOnePortableType($tuple1,$id2Portable[$tuple1],$id2Portable);
            $tuple2Type = array_key_exists($tuple2,$this->registeredSiType) ? $this->registeredSiType[$tuple2]:
                $this->dealOnePortableType($tuple2,$id2Portable[$tuple2],$id2Portable);
            // combine (a,b) Tuple
            $this->registeredSiType[$id] = sprintf("(%s,%s)",$tuple1Type,$tuple2Type);
            return $this->registeredSiType[$id];
        }
        // Compact
        if(array_key_exists("Compact",$one["def"])){
            $subType = intval($one["def"]["Compact"]["type"]);
            $this->registeredSiType[$id] = array_key_exists($subType,$this->registeredSiType) ? sprintf("Compact<%s>",$this->registeredSiType[$subType]):
                sprintf("Compact<%s>",$this->dealOnePortableType($subType,$id2Portable[$subType],$id2Portable));
            return $this->registeredSiType[$id];
        }
        // BitSequence
        if(array_key_exists("BitSequence",$one["def"])){
            $this->registeredSiType[$id] = "BitVec";
            return $this->registeredSiType[$id];
        }
        // Variant
        if(array_key_exists("Variant",$one["def"])){
            $VariantType = $one["path"][0];
            switch ($VariantType){
                // option
                case "Option":
                    $subType = intval($one["params"][0]["type"]);
                    $this->registeredSiType[$id] = array_key_exists($subType,$this->registeredSiType) ? sprintf("Option<%s>",$this->registeredSiType[$subType]):
                        sprintf("Option<%s>",$this->dealOnePortableType($subType,$id2Portable[$subType],$id2Portable));
                    return $this->registeredSiType[$id];
                // Result
                case "Result":
                    $ResultOk = intval($one["params"][0]["type"]);
                    $ResultErr = intval($one["params"][1]["type"]);
                    $okType = array_key_exists($ResultOk,$this->registeredSiType) ? $this->registeredSiType[$ResultOk]:
                        $this->dealOnePortableType($ResultOk,$id2Portable[$ResultOk],$id2Portable);
                    $errType = array_key_exists($ResultErr,$this->registeredSiType) ? $this->registeredSiType[$ResultErr]:
                        $this->dealOnePortableType($ResultErr,$id2Portable[$ResultErr],$id2Portable);
                    // combine (a,b) Tuple
                    $this->registeredSiType[$id] = sprintf("Result<%s,%s>",$okType,$errType);
                    return $this->registeredSiType[$id];
            }
            // pallet Call, Event, Error, metadata deal
            if(in_array(end($one["path"]),["Call", "Event", "Error"])){
                return "Call";
            }
            if(count($one["path"])==2 && (end($one["path"])== "Instruction" || (end($one["path"])=="Call" && $one["path"][count( $one["path"])-2]=="pallet") )){
                return "Call";
            }
            // Enum Todo
            foreach ($one["def"]["Variant"]["variants"] as $variant){

            }

        }
        $this->registeredSiType[$id] = "NULL";
        return "NULL";
    }
}


