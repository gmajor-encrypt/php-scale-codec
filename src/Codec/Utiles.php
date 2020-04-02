<?php

namespace Codec;


class Utiles
{
    public static function string2ByteArray($string)
    {
        return unpack('C*', $string);
    }

    public static function byteArray2String($byteArray)
    {
        $chars = array_map("chr", $byteArray);
        return join($chars);
    }

    /**
     * @param array $byteArray
     * @return string
     */
    public static function bytesToHex($byteArray)
    {
        $chars = array_map("chr", $byteArray);
        $bin = join($chars);
        return bin2hex($bin);
    }

    public static function hexToBytes($hexString)
    {
        $string = hex2bin($hexString);
        return unpack('C*', $string);
    }

    public static function string2Hex($string)
    {
        return bin2hex($string);
    }

    public static function hex2String($hexString)
    {
        return hex2bin($hexString);
    }

    /**
     * @param array $bytes
     * @return string
     */
    public static function bytesToCHex(array $bytes)
    {
        $hex = "";
        foreach ($bytes as $byte) {
            $hex .= sprintf("\\x" . dechex($byte));
        }
        return $hex;
    }


    /**
     * BytesToLittleInt
     * @param array $byteArray
     * @return int
     *
     *
     * v - unsigned short (always 16 bit, little endian byte order)
     * V - unsigned long (always 32 bit, little endian byte order)
     * P - unsigned long long (always 64 bit, little endian byte order)
     */
    public static function bytesToLittleInt(array $byteArray)
    {
        switch (count($byteArray)) {
            case 1:
                return $byteArray[1];
            case 2:
                return unpack("v", self::bytesToCHex($byteArray))[1];
            case 4:
                return unpack("V", self::bytesToCHex($byteArray))[1];
        }
        return 0;
    }
}
