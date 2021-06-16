<?php

namespace Codec;


use InvalidArgumentException;

class Base
{
    public const DEFAULT_NETWORK = 'default';

    protected static $defaultScaleTypes = array(
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
        "Metadata",
        "metadataV12",
        "V12Module"
    );

    /**
     * Create a new generator
     *
     * @param string $network
     *
     * @return Generator
     */
    public static function create ($network = ""): Generator
    {
        $generator = new Generator();
        foreach (static::$defaultScaleTypes as $scaleType) {
            $providerClassName = self::getScaleCodecClassname($scaleType, $network);
            $generator->addScaleType($scaleType, new $providerClassName($generator));
        }
        // interfaces runtime module types
        self::findInterfaces($generator);
        return $generator;
    }

    /**
     * @param string $scaleType
     * @param string $network
     * @return string
     */
    public static function getScaleCodecClassname (string $scaleType, $network = ''): string
    {
        if ($providerClass = self::findScaleCodecClassname($scaleType, $network)) {
            return $providerClass;
        }

        // fallback to default locale
        if ($providerClass = self::findScaleCodecClassname($scaleType, static::DEFAULT_NETWORK)) {
            return $providerClass;
        }

        $scaleType = self::convertPhpType($scaleType);
        // fallback to no locale
        if ($providerClass = self::findScaleCodecClassname($scaleType)) {
            return $providerClass;
        }
        throw new InvalidArgumentException(sprintf('Unable to find provider "%s" with network "%s"', $scaleType, $network));
    }

    /**
     * @param string $scaleType
     * @param string $network
     * @return string
     */
    protected static function findScaleCodecClassname (string $scaleType, $network = ''): string
    {
        $providerClass = 'Codec\\' . ($network ? sprintf('Types\%s\%s', $scaleType, $network) : sprintf('Types\%s', $scaleType));
        if (class_exists($providerClass, true)) {
            return $providerClass;
        }
        return "";
    }

    /**
     * convertPhpType
     *
     * @param $scaleType
     * @return mixed
     */
    private static function convertPhpType ($scaleType)
    {
        if (in_array($scaleType, ["Bool", "String", "Int"])) {
            return sprintf("T%s", $scaleType);
        }
        return $scaleType;
    }

    /**
     * findInterfaces
     *
     * @param Generator $generator
     */
    private static function findInterfaces (Generator $generator)
    {
        $moduleFiles = array_filter(Utils::getDirContents("src/Codec/interfaces/"), function ($var) {
            $slice = explode(".", $var);
            return $slice[count($slice) - 1] == "json";
        });
        foreach ($moduleFiles as $index => $file) {
            $content = json_decode(file_get_contents("src/Codec/interfaces/balances/definitions.json"), true);

        }
    }

    /**
     * regCustom
     *
     * @param Generator $generator
     * @param array $customJson
     */
    private static function regCustom (Generator $generator, array $customJson)
    {
        foreach ($customJson as $key => $value) {
            if (gettype($value) == "string") {
                $instant = $generator->getRegistry($value);
                if (!is_null($instant)) {
                    $generator->addScaleType($key, $instant);
                    continue;
                }
                // iteration
                if (array_key_exists($value, $customJson)) {
                    $explained = $customJson[$value];
                    if (gettype($explained) == "string") {
                        $instant = $generator->getRegistry($explained);
                        if (!is_null($instant)) {
                            $generator->addScaleType($key, $instant);
                            continue;
                        }
                    } else {
                        self::regCustom($generator, [$key => $explained]);
                    }
                }
                // Complex type
                if ($value[-1] == '>') {
                    $match = array();
                    preg_match("/^([^<]*)<(.+)>$/", $value, $match);
                    if (count($match) > 2) {
                        switch (strtolower($match[1])) {
                            case "vec":
                                $instant = $generator->getRegistry("vec");
                                $instant->subType = $match[2];
                                $generator->addScaleType($key, $instant);
                                break;
                            case "option":
                                $instant = $generator->getRegistry("option");
                                $instant->subType = $match[2];
                                $generator->addScaleType($key, $instant);
                                break;
                            case "compact":
                                $instant = $generator->getRegistry("compact");
                                $instant->subType = $match[2];
                                $generator->addScaleType($key, $instant);
                                break;
                            case "BTreeMap":
                                $instant = $generator->getRegistry("BTreeMap");
                                $instant->subType = $match[2];
                                $generator->addScaleType($key, $instant);
                                break;
                        }
                    }

                    // Tuple todo
                    // Fixed array todo
                }
            } elseif (gettype($value) == "array") { // todo
                if (array_key_exists("_enum", $customJson)) {

                }
                if (array_key_exists("_set", $customJson)) {

                }
            }
//              $generator->addScaleType($key,);
        }
    }


}

