<?php

namespace Codec\Types;

use BitWasp\Buffertools\Parser;
use Codec\ScaleBytes;
use Codec\Utils;
use GMP;

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
     * @return GMP|integer
     * @throws \Exception
     */
    public function decode ()
    {
        self::checkCompactBytes();
        if (!empty($this->subType)) {
            $data = $this->process($this->subType, new ScaleBytes($this->compactBytes));
            return (is_int($data) && $this->compactLength <= 4) ? Utils::ConvertGMP(intval($data / 4)) : Utils::ConvertGMP($data);
        }
        $UIntBitLength = 8 * $this->compactLength;
        foreach (range(4, 67) as $i) {
            if ($UIntBitLength >= 2 ** ($i - 1) && $UIntBitLength < 2 ** $i) {
                $UIntBitLength = 2 ** ($i - 1);
                break;
            }
        }
        $compactBytes = new ScaleBytes($this->compactBytes);
        if ($this->compactLength <= 4) {
            return gmp_init(intval($this->process("U{$UIntBitLength}", $compactBytes) / 4));
        }
        $parser = new Parser(Utils::bytesToHex($compactBytes->nextBytes($UIntBitLength / 8)));
        $value = $parser->readBytes($UIntBitLength / 8)->getGmp();
        return $value;
    }


    /**
     * checkCompactBytes
     */
    protected function checkCompactBytes ()
    {
        $compactBytes = $this->nextBytes(1);
        if (count($compactBytes) == 0) {
            throw new \OutOfRangeException('OutOfRangeException Compact');
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
     * @return \OutOfRangeException|string|null
     * @throws \Exception
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
            throw new \OutOfRangeException('Compact encode out of range');
        }
    }

}