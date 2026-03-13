<?php

namespace Codec;


use Exception;
use GMP;
use BitWasp\Buffertools\Buffer;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * Utils package
 * package of various tool functions
 *
 * Class Utils
 *
 * @package Codec
 */
class Utils
{

    /**
     *
     * Unpack data from a binary string
     *
     * @param string $str
     * @return array|false
     */
    public static function string2ByteArray(string $str): array|bool
    {
        return unpack('C*', $str);
    }


    /**
     * Bytes data to string
     *
     * @param $bytes
     * @return string
     */
    public static function byteArray2String($bytes): string
    {
        $chars = array_map("chr", $bytes);
        return join($chars);
    }

    /**
     * bytes data to hex string
     *
     * @param array $bytes
     * @return string
     */
    public static function bytesToHex(array $bytes): string
    {
        $chars = array_map("chr", $bytes);
        $bin = join($chars);
        return bin2hex($bin);
    }

    /**
     * hex string to bytes data
     *
     * @param $hex
     * @return array
     */
    public static function hexToBytes($hex): array
    {
        $string = hex2bin($hex);
        $value = unpack('C*', $string);
        return is_array($value) ? array_values($value) : [];
    }

    /**
     * Convert binary data into hexadecimal representation
     *
     * @param string $string
     * @return string
     */
    public static function string2Hex(string $string): string
    {
        return bin2hex($string);
    }

    /**
     *  Convert hexadecimal string to its binary representation.
     *
     * @param string $hexString
     * @return bool|string
     */
    public static function hex2String(string $hexString)
    {
        return hex2bin($hexString);
    }


    /**
     * @param $hexString string
     * @return string|string[]|null
     */
    public static function trimHex(string $hexString)
    {
        return preg_replace('/0x/', '', $hexString);
    }


    /**
     * BytesToLittleInt
     * bytes data to little int
     *
     * @param array $byteArray
     * @return int
     *
     *
     */
    public static function bytesToLittleInt(array $byteArray): int
    {
        switch (count($byteArray)) {
            case 1:
                return $byteArray[0];
            case 2: // uint16
                return $byteArray[0] | $byteArray[1] << 8;
            case 4: // uint32
                return $byteArray[0] | $byteArray[1] << 8 | $byteArray[2] << 16 | $byteArray[3] << 24;
            case 8: // uint64
                return $byteArray[0] | $byteArray[1] << 8 | $byteArray[2] << 16 | $byteArray[3] << 24 |
                    $byteArray[4] << 32 | $byteArray[5] << 40 | $byteArray[6] << 48 | $byteArray[7] << 56;
        }
        return 0;
    }

    /**
     * little int to bytes data
     *
     * @param int $value
     * @param int $length
     * @return string
     */
    public static function LittleIntToBytes(int $value, int $length)
    {
        switch ($length) {
            case 1:
                return self::bytesToHex(array($value));
            case 2:
                return self::bytesToHex(array($value, $value >> 8));
            case 4:
                return self::bytesToHex(array($value, $value >> 8, $value >> 16, $value >> 24));
            case 8:
                return self::bytesToHex(array($value, $value >> 8, $value >> 16, $value >> 24, $value >> 32, $value >> 40, $value >> 48, $value >> 56));
            default:
                return new OutOfRangeException('LittleIntToBytes');
        }
    }

    /**
     * padding $length-len($value) to left
     *
     * @param string $val
     * @param int $length
     * @return mixed
     */
    public static function padLeft(string $val, int $length): string
    {
        $fillUp = $length - strlen($val);
        return str_repeat("0", $fillUp) . $val;
    }

    /**
     * Convert GMP to hex
     *
     * @param GMP $value
     * @param int $length
     * @return string
     * @throws Exception
     */
    public static function LittleIntToHex(GMP $value, int $length)
    {
        $buffer = new Buffer(pack(
            "H*",
            str_pad(
                gmp_strval(
                    gmp_init(
                        self::flipBits(str_pad(
                            gmp_strval($value, 2),
                            $length * 8,
                            '0',
                            STR_PAD_LEFT
                        )),
                        2
                    ),
                    16
                ),
                $length * 2,
                '0',
                STR_PAD_LEFT
            )
        ));
        return self::trimHex($buffer->getHex());
    }

    /**
     * @param string $bitString
     * @return string
     * @throws Exception
     */
    private static function flipBits(string $bitString): string
    {
        $length = strlen($bitString);

        if ($length % 8 !== 0) {
            throw new Exception('Bit string length must be a multiple of 8');
        }

        $newString = '';
        for ($i = $length; $i >= 0; $i -= 8) {
            $newString .= substr($bitString, $i, 8);
        }

        return $newString;
    }

    /**
     * convert integer/string/object(GMP) to GMP
     *
     * @param int|string|GMP $value
     * @return GMP
     */

    public static function ConvertGMP($value): GMP
    {
        if (!in_array(gettype($value), ["integer", "string", "object"])) {
            throw new InvalidArgumentException("value must be one of type GMP|string|int");
        }
        if (gettype($value) == "object" && get_class($value) != "GMP") {
            throw new InvalidArgumentException("value must be one of type GMP|string|int");
        }
        return gettype($value) == "object" ? $value : gmp_init($value);
    }

    /**
     * getDirContents
     *
     * get dir all content file
     *
     * @param string $dir
     * @param array $results
     * @return array
     */
    public static function getDirContents(string $dir, array &$results = array()): array
    {
        $files = scandir($dir);
        foreach ($files as $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                self::getDirContents($path, $results);
            }
        }
        return $results;
    }

    /**
     * check array is assoc or not
     *
     * @param array $var
     * @return bool
     */
    public static function is_assoc(array $var): bool
    {
        return is_array($var) && array_diff_key($var, array_keys(array_keys($var)));
    }

    /**
     * string add prefix 0x
     *
     * @param string $s
     * @return string
     */
    public static function add_hex(string $s): string
    {
        // if string has prefix 0x, return directly
        if (str_starts_with($s, '0x')) {
            return $s;
        }
        return '0x' . $s;
    }
}

