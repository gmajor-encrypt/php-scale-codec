<?php
namespace Codec\Types;

use Codec\Base;
use Codec\Generator;

class ScaleInfo{


    /**
     * metadataV14 construct function
     *
     * @param Generator $generator
     */
    private Generator $generator;


    /**
     * has registered type name
     *
     * @var array
     */
    protected array $registeredTypeNames;

    /**
     * @var $registeredSiType array
     */
    public array $registeredSiType;

    public function __construct (Generator $generator)
    {
        $this->generator = $generator;
        $this->registeredTypeNames = [];
    }

    /**
     * regPortableType
     *
     * @param array $id2Portable
     */
    public function regPortableType (array $id2Portable)
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
            $this->dealOnePortableType($id, $item, $id2Portable);
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

        if (array_key_exists($id, $this->registeredSiType)) {
            return $this->registeredSiType[$id];
        }

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
     * @param int $siTypeId
     * @param void $one
     * @param void $id2Portable
     * @return string
     */
    private function genPathName (array $path, int $siTypeId,  $one, $id2Portable): string
    {
        if (is_array($one)) {
            if (array_key_exists("Variant", $one["type"]["def"]) && $one["type"]["path"][0] == "Option") {
                return self::expandOption($siTypeId, (array)$one["type"], (array)$id2Portable);
            }
        }
        $genName = join(":", $path);
        if (in_array($genName, $this->registeredTypeNames)) {
            $genName = $genName . "@" . $siTypeId;
        }
        return $genName;
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

        if (count($one["def"]["Composite"]["fields"]) == 0) {
            $this->registeredSiType[$id] = "NULL";
            return "NULL";
        }

        if (count($one["def"]["Composite"]["fields"]) == 1) {
            $siType = intval($one["def"]["Composite"]["fields"][0]["type"]);
            // check subType
            $subType = array_key_exists($siType, $this->registeredSiType) ? $this->registeredSiType[$siType] :
                $this->dealOnePortableType($siType, $id2Portable[$siType], $id2Portable);
            $typeString = self::genPathName($one["path"], $id,null,null);
            $this->registeredTypeNames[] = $typeString;
            Base::regCustom($this->generator, [$typeString => $subType]);
            $this->registeredSiType[$id] = $typeString;
            return $subType;
        }
        $tempStruct = [];
        foreach ($one["def"]["Composite"]["fields"] as $field) {
            $tempStruct[$field["name"]] = array_key_exists($field["type"], $this->registeredSiType) ? $this->registeredSiType[$field["type"]] :
                $this->dealOnePortableType($field["type"], $id2Portable[$field["type"]], $id2Portable);
        }
        $instant = clone $this->generator->getRegistry("struct");
        $instant->typeStruct = $tempStruct;
        $typeString = self::genPathName($one["path"], $id,null,null);
        $this->registeredTypeNames[] = $typeString;
        $this->generator->addScaleType($typeString, $instant);
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
                        self::genPathName($id2Portable[$siType]["type"]["path"], $siType,null,null);
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
                                self::genPathName($id2Portable[$siType]["type"]["path"], $siType,null,null);
                        }
                        $enumValueList[$name] = sprintf("(%s)", $typeMapping);
                        break;
                    }

                    $typeMapping = [];
                    foreach ($variant["fields"] as $field) {
                        $valueName = $field["name"];
                        $siType = $field["type"];
                        $typeMapping[$valueName] = array_key_exists($siType, $this->registeredSiType) ? $this->registeredSiType[$siType] : self::genPathName($id2Portable[$siType]["type"]["path"], $siType,$id2Portable[$siType],$id2Portable);
                    }
                    $enumValueList[$name] = json_encode($typeMapping);
                    break;
            }
        }


        $instant = clone $this->generator->getRegistry("enum");
        $instant->typeStruct = $enumValueList;
        $typeString = self::genPathName($one["path"], $id,null,null);
        $this->registeredTypeNames[] = $typeString;
        $this->generator->addScaleType($typeString, $instant);
        $this->registeredSiType[$id] = $typeString;
        return $typeString;
    }

}
