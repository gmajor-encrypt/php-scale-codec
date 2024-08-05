<?php

namespace Codec;


use InvalidArgumentException;
class Base
{
    public const DEFAULT_NETWORK = 'default';


    // default registered types
    // include basic types and EventRecord, MetadataV12, MetadataV13, Extrinsic
    protected static array $defaultScaleTypes = array(
        "Compact",
        "Option",
        "Bytes",
        "String",
        "Struct",
        "Bool",
        "Enum",
        "Set",
        "Vec",
        "Tuples",
        "H256",
        "BTreeMap",
        "VecU8Fixed",
        "Address", "AccountId", "GenericMultiAddress",
        "U8", "U16", "U32", "U64", "U128",
        "Int", "I8", "I16", "I32", "I64", "I128",
        "StorageHasher",
        "OptionBool",
        "Metadata", "metadataV12", "metadataV13", "V12Module", "ModuleStorage", "MetadataModuleStorageEntry", "MetadataModuleCall",
        "MetadataModuleCallArgument", "MetadataModuleConstants", "MetadataModuleEvent", "MetadataModuleConstants",
        "MetadataModuleError", "EraExtrinsic", "EventRecord", "Extrinsic","BitVec",
        "FixedArray", "Null", "Result","metadataV14","V14Module","MetadataV14ModuleStorage","MetadataV14ModuleStorageEntry",
        "Call","Data"
    );

    /**
     * Create a new scale generator
     *
     * @param string $network
     *
     * @return Generator
     */
    public static function create (string $network = ""): Generator
    {
        $generator = new Generator();
        //  register default types
        foreach (static::$defaultScaleTypes as $scaleType) {
            $providerClassName = self::getScaleCodecClassname($scaleType, $network);
            $generator->addScaleType($scaleType, new $providerClassName($generator));
        }
        // interfaces runtime module types
        self::findInterfaces($generator);
        return $generator;
    }

    /**
     * get scale codec Classname
     *
     * @param string $scaleType
     * @param string $network
     * @return string
     */
    public static function getScaleCodecClassname (string $scaleType, string $network = ''): string
    {
        if ($providerClass = self::findScaleCodecClassname($scaleType, $network)) {
            return $providerClass;
        }

        // fallback to default locale
        if ($providerClass = self::findScaleCodecClassname($scaleType, static::DEFAULT_NETWORK)) {
            return $providerClass;
        }
        // convert class name, avoid php keyword conflict
        $scaleType = self::convertPhpType($scaleType);
        // fallback to no locale
        if ($providerClass = self::findScaleCodecClassname($scaleType)) {
            return $providerClass;
        }
        // throw error
        throw new InvalidArgumentException(sprintf('Unable to find provider "%s" with network "%s"', $scaleType, $network));
    }

    /**
     * find scale codec Classname
     * default from src/Codec/Types dir
     *
     * @param string $scaleType
     * @param string $network
     * @return string
     */
    protected static function findScaleCodecClassname (string $scaleType, string $network = ''): string
    {
        $providerClass = 'Codec\\' . ($network ? sprintf('Types\%s\%s', $scaleType, $network) : sprintf('Types\%s', $scaleType));
        if (class_exists($providerClass, true)) {
            return $providerClass;
        }
        return "";
    }

    /**
     * convertPhpType
     * convert scale type name avoid php keyword conflict
     * so bool, string, int, null rename to Tbool, Tstring, Tint, Tnull
     *
     * @param $scaleType
     * @return mixed
     */
    private static function convertPhpType ($scaleType): mixed
    {
        if (in_array($scaleType, ["Bool", "String", "Int", "Null"])) {
            return sprintf("T%s", $scaleType);
        }
        return $scaleType;
    }

    /**
     * findInterfaces
     * find pallets custom types from file, default dir src/Codec/interfaces/
     *
     *
     * @param Generator $generator
     */
    private static function findInterfaces (Generator $generator)
    {
        // find all json file from dir
        $moduleFiles = array_filter(Utils::getDirContents(__DIR__ . "/interfaces/"), function ($var) {
            $slice = explode(".", $var);
            return $slice[count($slice) - 1] == "json";
        });
        $moduleTypes = [];
        foreach ($moduleFiles as $file) {
            $content = json_decode(file_get_contents($file), true);
            // merge all array to one $moduleTypes array
            $moduleTypes = array_merge($moduleTypes, $content);
        }
        // reg custom type
        self::regCustom($generator, $moduleTypes);
    }

    /**
     * regCustom
     * reg all custom type from param $customJson
     * About custom type of https://github.com/gmajor-encrypt/php-scale-codec/blob/master/custom_type.md can be found here
     *
     * @param Generator $generator
     * @param array $customJson
     */
    public static function regCustom (Generator $generator, array $customJson)
    {
        foreach ($customJson as $key => $value) {
            if (gettype($value) == "string") {
                $instant = $generator->getRegistry($value);
                if (!is_null($instant)) {
                    $generator->addScaleType($key, $instant);
                    continue;
                }
                // iteration
                while (true) {
                    if (array_key_exists($value, $customJson)) {
                        $value = $customJson[$value];
                        if (gettype($value) == "string") {
                            $instant = $generator->getRegistry($value);
                            if (!is_null($instant)) {
                                $generator->addScaleType($key, $instant);
                                $iterationSolve = true;
                                break;
                            } else {
                                continue;
                            }
                        }
                        self::regCustom($generator, [$key => $value]);
                        $iterationSolve = true;
                    } else {
                        $iterationSolve = false;
                    }
                    break;
                }
                if ($iterationSolve) {
                    continue;
                }

                // Complex type
                if ($value[-1] == '>') {
                    $match = array();
                    //  find sub types
                    preg_match("/^([^<]*)<(.+)>$/", $value, $match);
                    if (count($match) > 2) {
                        switch (strtolower($match[1])) {
                            // vec array
                            case "vec":
                                $instant = clone $generator->getRegistry("vec");
                                $instant->subType = $match[2];
                                $generator->addScaleType($key, $instant);
                                break;
                            // option
                            case "option":
                                $instant = clone $generator->getRegistry("option");
                                $instant->subType = $match[2];
                                $generator->addScaleType($key, $instant);
                                break;
                            // compact
                            case "compact":
                                $instant = clone $generator->getRegistry("compact");
                                $instant->subType = $match[2];
                                $generator->addScaleType($key, $instant);
                                break;
                            // BTreeMap
                            case "btreemap":
                                $instant = clone $generator->getRegistry("bTreeMap");
                                $instant->subType = $match[2];
                                $generator->addScaleType($key, $instant);
                                break;
                            case "result":
                                $instant = clone $generator->getRegistry("Result");
                                $instant->subType = $match[2];
                                $generator->addScaleType($key, $instant);
                                break;
                        }
                        continue;
                    }
                }

                // Tuple
                if ($value[0] == '(' && $value[-1] == ')') {
                    $instant = clone $generator->getRegistry('tuples');
                    $instant->typeString = $value;
                    $instant->buildTuplesMapping();
                    $generator->addScaleType($key, $instant);
                    continue;
                }
                // Fixed array
                if ($value[0] == '[' && $value[-1] == ']') {
                    $slice = explode(";", substr($value, 1, strlen($value) - 2));
                    if (count($slice) == 2) {
                        $subType = trim($slice[0]);
                        $instant = strtolower($subType) == "u8" ? clone $generator->getRegistry('VecU8Fixed') : clone $generator->getRegistry('FixedArray');
                        $instant->subType = trim($slice[0]);
                        $instant->FixedLength = intval($slice[1]);
                        $generator->addScaleType($key, $instant);
                    }
                }
            } elseif (gettype($value) == "array") {
                // enum
                if (array_key_exists("_enum", $value)) {
                    $instant = clone $generator->getRegistry("enum");
                    Utils::is_assoc($value["_enum"]) ? $instant->typeStruct = $value["_enum"] : $instant->valueList = $value["_enum"];
                    $generator->addScaleType($key, $instant);
                    continue;
                }
                // set
                if (array_key_exists("_set", $value)) {
                    $instant = clone $generator->getRegistry("set");
                    array_key_exists("_bitLength", $value["_set"]) ? $instant->BitLength = intval($value["_set"]["_bitLength"]) : $instant->BitLength = 16;
                    unset($value["_set"]["_bitLength"]);
                    $instant->valueList = array_keys($value["_set"]);
                    $generator->addScaleType($key, $instant);
                    continue;
                }
                // struct
                $instant = clone $generator->getRegistry("struct");
                $instant->typeStruct = $value;
                $generator->addScaleType($key, $instant);
            }
        }
    }


}

