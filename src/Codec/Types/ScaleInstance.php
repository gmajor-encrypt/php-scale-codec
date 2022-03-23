<?php

namespace Codec\Types;

use Codec\Generator;
use Codec\ScaleBytes;
use Codec\Utils;

class ScaleInstance implements CodecInterface
{
    /**
     * @var Generator
     */
    protected Generator $generator;

    /**
     * @var ScaleBytes $data
     */
    protected ScaleBytes $data;

    /**
     * @var string $typeString
     */
    public string $typeString;

    /**
     * @var string $subType
     */
    public string $subType;

    /**
     * @var mixed $value
     */
    public mixed $value;

    /**
     *
     * @var array $metadata
     */
    protected array $metadata;


    /**
     * @var array $typeStruct
     */
    public array $typeStruct;


    /**
     * @var array $typeStruct
     * enum
     */
    public array $valueList;

    /**
     * @var int
     * Set struct BitLength
     */
    public int $BitLength;


    /**
     * @var int
     * Fixed int FixedLength
     */
    public int $FixedLength;

    /**
     * ScaleDecoder constructor.
     *
     * @param Generator $generator
     */
    public function __construct (Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param ScaleBytes $data
     * @param string $subType
     * @param $metadata
     */
    public function init (ScaleBytes $data, string $subType = "", $metadata = null)
    {
        $this->data = $data;
        if (!empty($subType)) {
            $this->subType = $subType;
        }
        if (!is_null($metadata)) {
            $this->metadata = $metadata;
        }
    }

    /**
     * buildStructMapping
     */
    public function buildTuplesMapping ()
    {
        $typeStruct = [];
        foreach (explode(",", substr($this->typeString, 1, strlen($this->typeString) - 2)) as $key => $element) {
            $typeStruct[] = trim($element);
        }
        $this->typeStruct = $typeStruct;
    }

    /**
     * @param string $typeString
     * @param ScaleBytes|null $codecData |null
     * @param array $metadata
     * @return mixed
     */
    public function process (string $typeString, ScaleBytes $codecData = null, array $metadata = null)
    {
        $codecInstant = self::createTypeByTypeString($typeString);
        $codecInstant->typeString = $typeString;
        if ($codecData == null) {
            $codecData = $this->data;
        }
        if (!empty($this->metadata) && empty($metadata)) {
            $metadata = $this->metadata;
        }
        $codecInstant->init($codecData, "", $metadata);
        return $codecInstant->decode();
    }

    /**
     * createTypeByTypeString
     *
     * @param string $typeString
     * @return ScaleInstance
     */
    public function createTypeByTypeString (string $typeString)
    {
        $typeString = self::convertType($typeString);
        $match = array();

        if ($typeString[-1] == '>') {
            $codecInstant = $this->generator->getRegistry(strtolower($typeString));
            if (!is_null($codecInstant)) {
                return clone $codecInstant;
            }
            preg_match("/^([^<]*)<(.+)>$/", $typeString, $match);
        }
        if (count($match) > 0) {
            $codecInstant = $this->generator->getRegistry(strtolower($match[1]));
            if (!is_null($codecInstant)) {
                $codecInstant = clone $codecInstant;
                $codecInstant->subType = $match[2];
                return $codecInstant;
            }
        } else {
            $codecInstant = $this->generator->getRegistry(strtolower($typeString));
            if (!is_null($codecInstant)) {
                return clone $codecInstant;
            }
        }

        if ($typeString[0] == '(' && $typeString[-1] == ')') {
            echo "\nTuple TypeString: " . $typeString;
            $struct = $this->generator->getRegistry('tuples');
            $struct->typeString = $typeString;
            echo "\nStruct Type:" . json_encode($struct);
            $struct->buildTuplesMapping();
            echo "\nTupple mapping:" . json_encode($struct);
            return $struct;
        }

        if ($typeString[0] == '[' && $typeString[-1] == ']') {
            $slice = explode(";", substr($typeString, 1, strlen($typeString) - 2));
            echo "\nTypeString: " . $typeString;
            if (count($slice) == 2) {
                echo "\nSlice: " . json_encode($slice);
                $subType = trim($slice[0]);
                $instant = strtolower($subType) == "u8" ? $this->generator->getRegistry('VecU8Fixed') : $this->generator->getRegistry('FixedArray');
                $instant->subType = $subType;
                $instant->FixedLength = intval($slice[1]);
                return $instant;
            }
        }


        throw new \InvalidArgumentException(sprintf('Unknown codec type "%s"', $typeString));
    }


    /**
     * @param $length
     * @return array
     */
    protected function nextBytes ($length): array
    {
        return $this->data->nextBytes($length);
    }

    /**
     * @return int
     */
    protected function remainBytesLength (): int
    {
        return $this->data->remainBytesLength();
    }

    /**
     * nextU8
     *
     * @return int
     */
    protected function nextU8 ()
    {
        return Utils::bytesToLittleInt($this->nextBytes(1));
    }

    /**
     * nextBool
     *
     * check next first bytes is 0, return True when bytes is 1
     *
     * @return bool
     */
    protected function nextBool ()
    {
        $data = $this->nextBytes(1);
        if (!in_array($data[0], [0, 1])) {
            throw new \UnexpectedValueException(sprintf('InValid value "%s" type bool', $data[0]));
        }
        return $data[0] === 1;
    }


    /**
     * convertType
     *
     * @param string $typeString
     * @return string
     */
    private static function convertType (string $typeString)
    {
        if ($typeString == '()') {
            return "Null";
        }
        $typeString = str_replace("T::", "", $typeString);
        $typeString = str_replace("VecDeque<", "Vec<", $typeString);
        $typeString = str_replace("<T>", "", $typeString);
        $typeString = str_replace("<T, I>", "", $typeString);
        $typeString = str_replace("&'static[u8]", "Bytes", $typeString);
        switch ($typeString) {
            case "<Lookup as StaticLookup>::Source":
                return "Address";
        }
        return $typeString;
    }


    /**
     * @return mixed
     */
    public function decode ()
    {
        return;
        // TODO: Implement decode() method.
    }

    public function encode ($param)
    {
        return null;
    }
}