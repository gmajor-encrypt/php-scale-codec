<?php


namespace Codec\Types;


class MetadataV14ModuleStorageEntry extends Struct
{
    /**
     *
     *
     * MetadataV14ModuleStorageEntry
     *
     * MetadataModuleStorageEntry declares storage struct and this value hasher is a enum
     * there 2 Storage type PlainType, Map
     *
     * {
     *  "name":"string",
     *  "modifier":"StorageModify",
     *  "type":"StorageFunctionTypeV14", // PlainType or Map
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
        switch ($this->process("StorageFunctionTypeV14")) {
            case "PlainType":
                $value["type"] = [
                    "origin" => "PlainType",
                    "plain_type_id" => $this->process("SiLookupTypeId"),
                ];
                break;
            case "Map":
                $value["type"] = [
                    "origin" => "Map",
                    "MapType" => [
                        "hashers" => $this->process("vec<StorageHasher>"),
                        "keys_id" => $this->process("SiLookupTypeId"),
                        "values_id" => $this->process("SiLookupTypeId"),
                    ]
                ];
                break;
        }

        $value["fallback"] = $this->process("Bytes");
        $value["docs"] = $this->process("Vec<string>");
        return $value;
    }


    /**
     * MetadataV14ModuleStorageEntry encode
     *
     * @param $param
     * @return \InvalidArgumentException|mixed|string|null
     */

    public function encode ($param)
    {
        $name = $this->createTypeByTypeString("String")->encode($param["name"]);
        $StorageModify = $this->createTypeByTypeString("StorageModify")->encode($param["modifier"]);

        $hashType = $param["type"];
        $StorageFunctionType = $this->createTypeByTypeString("StorageFunctionTypeV14")->encode($hashType["origin"]);

        $hashFunctionValue = "";

        switch ($hashType["origin"]) {
            case "PlainType":
                $hashFunctionValue = $this->createTypeByTypeString("SiLookupTypeId")->encode($hashType["plain_type_id"]);
                break;
            case "Map":
                $hashFunctionValue = $this->createTypeByTypeString("vec<StorageHasher>")->encode($hashType["MapType"]["hashers"]) .
                    $this->createTypeByTypeString("SiLookupTypeId")->encode($hashType["MapType"]["keys_id"]) .
                    $this->createTypeByTypeString("SiLookupTypeId")->encode($hashType["MapType"]["values_id"]);
                break;
        }

        $fallback = $this->createTypeByTypeString("Bytes")->encode($param["fallback"]);
        $docs = $this->createTypeByTypeString("Vec<string>")->encode($param["docs"]);

        return $name . $StorageModify . $StorageFunctionType . $hashFunctionValue . $fallback . $docs;
    }

}
