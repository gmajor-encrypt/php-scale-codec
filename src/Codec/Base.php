<?php

namespace Codec;


class Base
{
    const DEFAULT_NETWORK = 'default';

    protected static $defaultScaleTypes = array(
        "Compact",
        "CompactU32",
        "Option",
        "Bytes",
        "String",
        "HexBytes",
        "U8",
        "U16",
        "U32",
        "Struct",
        "Bool",
        "Enum",
        "Set",
        "Address",
        "Vec",
        "U64",
        "Int",
        "BTreeMap",
        "VecU8Fixed",
        "AccountId",
        "U128",
        "StorageHasher",
        "H256",
        "GenericMultiAddress"
    );

    /**
     * Create a new generator
     *
     * @param string $network
     *
     * @return Generator
     */
    public static function create ($network = "")
    {
        $generator = new Generator();
        foreach (static::$defaultScaleTypes as $scaleType) {
            $providerClassName = self::getScaleCodecClassname($scaleType, $network);
            $generator->addScaleType($scaleType, new $providerClassName($generator));
        }

        return $generator;
    }

    /**
     * @param string $scaleType
     * @param string $network
     * @return string
     */
    public static function getScaleCodecClassname ($scaleType, $network = '')
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
        throw new \InvalidArgumentException(sprintf('Unable to find provider "%s" with network "%s"', $scaleType, $network));
    }

    /**
     * @param string $scaleType
     * @param string $network
     * @return string
     */
    protected static function findScaleCodecClassname ($scaleType, $network = '')
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
}

