<?php

namespace Codec\Types;

use BitWasp\Buffertools\Parser;
use Codec\ScaleBytes;
use Codec\Utils;
use Exception;
use GMP;
use OutOfRangeException;


/**
 * Class Compact
 *
 * @package Codec\Types
 *
 * A "compact" or general integer encoding is sufficient for encoding large integers (up to 2**536) and is more efficient at encoding most values than the fixed-width version.
 *
 * It is encoded with the two least significant bits denoting the mode:
 * 0b00: single-byte mode; upper six bits are the LE encoding of the value (valid only for values of 0-63).
 * 0b01: two-byte mode: upper six bits and the following byte is the LE encoding of the value (valid only for values 64-(2**14-1)).
 * 0b10: four-byte mode: upper six bits and the following three bytes are the LE encoding of the value (valid only for values (2**14)-(2**30-1)).
 * 0b11: Big-integer mode: The upper six bits are the number of bytes following, less four. The value is contained, LE encoded, in the bytes following. The final (most significant) byte must be non-zero. Valid only for values (2**30)-(2**536-1).
 *
 * https://substrate.dev/docs/en/knowledgebase/advanced/codec#compactgeneral-integers
 *
 */
class Compact extends ScaleInstance
{
    /**
     * @var integer $compactLength
     */
    protected $compactLength;

    /**
     * @var array $compactBytes
     */
    protected $compactBytes;

    /**
     * @return GMP|string
     * @throws Exception
     */
    public function decode (): GMP|string
    {
        self::checkCompactBytes();
        $compactBytes = new ScaleBytes($this->compactBytes);
        if (!empty($this->subType)) {
            $data = Utils::bytesToLittleInt($compactBytes->nextBytes(8));
            return (is_int($data) && $this->compactLength <= 4) ? gmp_strval(Utils::ConvertGMP(intval($data / 4))) : gmp_strval(Utils::ConvertGMP($data));
        }
        $UIntBitLength = 8 * $this->compactLength;
        if ($this->compactLength <= 4) {
            foreach (range(4, 67) as $i) {
                if ($UIntBitLength >= 2 ** ($i - 1) && $UIntBitLength < 2 ** $i) {
                    $UIntBitLength = 2 ** ($i - 1);
                    break;
                }
            }
            return gmp_init(intval($this->process("U{$UIntBitLength}", $compactBytes) / 4));
        }
        $parser = new Parser(Utils::bytesToHex($compactBytes->nextBytes($UIntBitLength / 8)));
        return $parser->readBytes($UIntBitLength / 8, true)->getGmp();
    }

    /**
     * checkCompactBytes
     */
    protected function checkCompactBytes ()
    {
        $compactBytes = $this->nextBytes(1);
        if (count($compactBytes) == 0) {
            throw new OutOfRangeException('OutOfRangeException Compact');
        }
        $mod = $compactBytes[0] % 4;

        switch ($mod) {
            case 0:
                $this->compactLength = 1;
                break;
            case 1:
                $this->compactLength = 2;
                break;
            case 2:
                $this->compactLength = 4;
                break;
            default:
                $this->compactLength = intval(5 + ($compactBytes[0] - 3) / 4);
        }

        switch ($this->compactLength) {
            case 1:
                $this->compactBytes = $compactBytes;
                break;
            case in_array($this->compactLength, [2, 4]):
                array_push($compactBytes, ...$this->nextBytes($this->compactLength - 1));
                $this->compactBytes = $compactBytes;
                break;
            default:
                $this->compactBytes = $this->nextBytes($this->compactLength - 1);
        }
    }


    /**
     * Compact encode
     *
     * @param GMP|string|int $param
     * @return OutOfRangeException|string|null
     * @throws Exception
     *
     * https://substrate.dev/docs/en/knowledgebase/advanced/codec#compactgeneral-integers
     */
    public function encode ($param)
    {
        $value = $param;
        if (!in_array(gettype($value), ["integer", "string", "object"])) {
            throw new \InvalidArgumentException("value must be one of type GMP|string|int");
        }
        if (gettype($value) == "object" && get_class($value) != "GMP") {
            throw new \InvalidArgumentException("value must be one of type GMP|string|int");
        } else {
            $value = gmp_sub($value, "1073741824") < 0 ? gmp_intval($value) : $value;
        }
        if (is_string($value)) {
            $value = gmp_init($value);
        }
        if (gmp_sub($value, "64") < 0) { //2**6-1
            return Utils::LittleIntToHex(gmp_init($value << 2), 1);
        } elseif (gmp_sub($value, "16384") < 0) { //2**14-1
            return Utils::LittleIntToHex(gmp_init($value << 2 | 1), 2);
        } elseif (gmp_sub($value, "1073741824") < 0) { // 2**30-1
            return Utils::LittleIntToHex(gmp_init($value << 2 | 2), 4);
        } elseif (gmp_sub($value, gmp_pow("2", 536)) < 0) {
            foreach (range(4, 67) as $i) {
                if (gmp_cmp($value, gmp_pow("2", 8 * ($i - 1))) != -1 && gmp_cmp($value, gmp_pow("2", 8 * $i)) == -1) {
                    return Utils::LittleIntToBytes(($i - 4) << 2 | 3, 1) . Utils::LittleIntToHex($value, $i);
                }
            }
        } else {
            throw new OutOfRangeException('Compact encode out of range');
        }
    }

}