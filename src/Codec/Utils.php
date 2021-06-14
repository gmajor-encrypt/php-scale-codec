<?php

namespace Codec;


use Exception;
use GMP;
use BitWasp\Buffertools\Buffer;
use InvalidArgumentException;
use OutOfRangeException;

class Utils
{

    /**
     * @param string $str
     * @return array|false
     */
    public static function string2ByteArray (string $str)
    {
        return unpack('C*', $str);
    }


    /**
     * @param $bytes
     * @return string
     */
    public static function byteArray2String ($bytes): string
    {
        $chars = array_map("chr", $bytes);
        return join($chars);
    }

    /**
     * @param array $bytes
     * @return string
     */
    public static function bytesToHex (array $bytes): string
    {
        $chars = array_map("chr", $bytes);
        $bin = join($chars);
        return bin2hex($bin);
    }

    /**
     * @param $hex
     * @return array
     */
    public static function hexToBytes ($hex): array
    {
        $string = hex2bin($hex);
        $value = unpack('C*', $string);
        return is_array($value) ? array_values($value) : [];
    }

    /**
     * @param string $string
     * @return string
     */
    public static function string2Hex (string $string): string
    {
        return bin2hex($string);
    }

    /**
     * @param string $hexString
     * @return bool|string
     */
    public static function hex2String (string $hexString)
    {
        return hex2bin($hexString);
    }


    /**
     * @param $hexString string
     * @return string|string[]|null
     */
    public static function trimHex (string $hexString)
    {
        return preg_replace('/0x/', '', $hexString);
    }


    /**
     * BytesToLittleInt
     *
     * @param array $byteArray
     * @return int
     *
     *
     */
    public static function bytesToLittleInt (array $byteArray): int
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
     * @param int $value
     * @param int $length
     * @return string
     */
    public static function LittleIntToBytes (int $value, int $length)
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
                return new OutOfRangeException(sprintf('LittleIntToBytes'));
        }
    }

    /**
     * @param string $val
     * @param int $length
     * @return mixed
     */
    public static function padLeft (string $val, int $length): string
    {
        $fillUp = $length - strlen($val);
        return str_repeat("0", $fillUp) . $val;
    }

    /**
     * @param GMP $value
     * @param int $length
     * @return string
     * @throws Exception
     */
    public static function LittleIntToHex (GMP $value, int $length)
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
    public static function flipBits (string $bitString): string
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
     * @param int|string|GMP $value
     * @return GMP
     */

    public static function ConvertGMP($value):GMP
    {
        if (!in_array(gettype($value), ["integer", "string", "object"])) {
            throw new InvalidArgumentException("value must be one of type GMP|string|int");
        }
        if (gettype($value) == "object" && get_class($value) != "GMP") {
            throw new InvalidArgumentException("value must be one of type GMP|string|int");
        }
        return gettype($value) == "object"? $value: gmp_init($value);
    }
}
