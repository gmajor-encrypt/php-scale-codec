<?php


namespace Codec\Types;


class MetadataModuleStorageEntry extends Struct
{
    /**
     * MetadataModuleStorageEntry
     * {
     *  "name":"string",
     *  "modifier":"StorageModify",
     *  "type":"StorageFunctionType",
     *  "fallback":"Bytes",
     *  "docs":"Vec<string>",
     * }
     *
     *
     */


    public function decode (): array
    {
        $value = [];
        $value["name"] = $this->process("String");
        $value["modifier"] = $this->process("StorageModify");

        switch ($this->process("StorageFunctionType")) {
            case "MapType":
                $value["type"] = [
                    "origin" => "MapType",
                    "map_type" => [
                        "hasher" => $this->process("StorageHasher"),
                        "key" => $this->process("string"),
                        "value" => $this->process("string"),
                        "isLinked" => $this->process("bool"),
                    ]
                ];
                break;
            case "DoubleMapType":
                $value["type"] = [
                    "origin" => "DoubleMapType",
                    "double_map_type" => [
                        "hasher" => $this->process("StorageHasher"),
                        "key1" => $this->process("string"),
                        "key2" => $this->process("string"),
                        "value" => $this->process("string"),
                        "key2Hasher" => $this->process("StorageHasher"),
                    ]
                ];
                break;
            case "PlainType":
                $value["type"] = [
                    "origin" => "PlainType",
                    "plain_type" => $this->process("string"),
                ];
                break;
            case "NMap":
                $value["type"] = [
                    "origin" => "NMapType",
                    "NMapType" => [
                        "keyVec" => $this->process("Vec<String>"),
                        "hashers" => $this->process("vec<StorageHasher>"),
                        "value" => $this->process("string"),
                    ]
                ];
                break;
        }

        $value["fallback"] = $this->process("Bytes");
        $value["docs"] = $this->process("Vec<string>");
        return $value;
    }


    /**
     * MetadataModuleStorageEntry encode
     *
     * @param $param
     * @return \InvalidArgumentException|mixed|string|null
     */

    public function encode ($param)
    {
        $name = $this->createTypeByTypeString("String")->encode($param["name"]);
        $StorageModify = $this->createTypeByTypeString("StorageModify")->encode($param["modifier"]);

        $hashType = $param["type"];
        $StorageFunctionType = $this->createTypeByTypeString("StorageFunctionType")->encode($hashType["origin"]);

        //
        $hashFunctionValue = "";

        switch ($hashType["origin"]) {
            case "MapType":
                $hashFunctionValue = $this->createTypeByTypeString("StorageHasher")->encode($hashType["map_type"]["hasher"]) .
                    $this->createTypeByTypeString("String")->encode($hashType["map_type"]["key"]) .
                    $this->createTypeByTypeString("String")->encode($hashType["map_type"]["value"]) .
                    $this->createTypeByTypeString("bool")->encode($hashType["map_type"]["isLinked"]);
                break;
            case "DoubleMapType":
                $hashFunctionValue = $this->createTypeByTypeString("StorageHasher")->encode($hashType["double_map_type"]["hasher"]) .
                    $this->createTypeByTypeString("String")->encode($hashType["double_map_type"]["key1"]) .
                    $this->createTypeByTypeString("String")->encode($hashType["double_map_type"]["key2"]) .
                    $this->createTypeByTypeString("string")->encode($hashType["double_map_type"]["value"]) .
                    $this->createTypeByTypeString("StorageHasher")->encode($hashType["double_map_type"]["key2Hasher"]);
                break;
            case "PlainType":
                $hashFunctionValue = $this->createTypeByTypeString("String")->encode($hashType["plain_type"]);
                break;
            case "NMap":
                $hashFunctionValue = $this->createTypeByTypeString("Vec<String>")->encode($hashType["NMapType"]["keyVec"]) .
                    $this->createTypeByTypeString("vec<StorageHasher>")->encode($hashType["NMapType"]["hashers"]) .
                    $this->createTypeByTypeString("String")->encode($hashType["NMapType"]["value"]);
                break;
        }

        $fallback = $this->createTypeByTypeString("Bytes")->encode($param["fallback"]);
        $docs = $this->createTypeByTypeString("Vec<string>")->encode($param["docs"]);

        return $name . $StorageModify . $StorageFunctionType . $hashFunctionValue . $fallback . $docs;
    }
}
