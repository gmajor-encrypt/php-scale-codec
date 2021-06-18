<?php


namespace Codec\Types;


class MetadataModuleStorageEntry extends ScaleInstance
{
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
}
