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
    protected $generator;

    /**
     * @var ScaleBytes $data
     */
    protected $data;

    /**
     * @var string $typeString
     */
    public $typeString;

    /**
     * @var string $subType
     */
    protected $subType;

    /**
     * @var mixed $value
     */
    public $value;

    /**
     *
     * @var array $metadata
     */
    protected $metadata;


    /**
     * @var array $typeStruct
     */
    public $typeStruct;


    /**
     * @var array $typeStruct
     * enum
     */
    public $valueList;

    /**
     * @var int
     * Set struct BitLength
     */
    public $BitLength;


    /**
     * @var int
     * Fixed int FixedLength
     */
    public $FixedLength;

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
        $this->metadata = $metadata;
    }

    /**
     * buildStructMapping
     */
    protected function buildTuplesMapping ()
    {
        $typeStruct = [];
        foreach (explode(",", substr($this->typeString, 1, strlen($this->typeString) - 2)) as $key => $element) {
            array_push($typeStruct, str_replace(';', ',', trim($element)));
        }
        $this->typeStruct = $typeStruct;
    }

    /**
     * @param string $typeString
     * @param ScaleBytes|null $codecData |null
     * @param array $option
     * @return mixed
     */
    public function process (string $typeString, ScaleBytes $codecData = null, array $option = [])
    {
        $codecInstant = self::createTypeByTypeString($typeString);
        $codecInstant->typeString = $typeString;
        if ($codecData == null) {
            $codecData = $this->data;
        }
        $codecInstant->init($codecData);
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
                return $codecInstant;
            }
            preg_match("/^([^<]*)<(.+)>$/", $typeString, $match);
        }
        if (count($match) > 0) {
            $codecInstant = $this->generator->getRegistry(strtolower($match[1]));
            if (!is_null($codecInstant)) {
                $codecInstant->subType = $match[2];
                return $codecInstant;
            }
        } else {
            $codecInstant = $this->generator->getRegistry(strtolower($typeString));
            if (!is_null($codecInstant)) {
                return $codecInstant;
            }
        }

        if ($typeString[0] == '(' && $typeString[-1] == ')') {
            $struct = $this->generator->getRegistry('tuples');
            $struct->typeString = $typeString;
            $struct->buildTuplesMapping();
            return $struct;
        }


        throw new \InvalidArgumentException(sprintf('Unknown codec type "%s"', $typeString));
    }


    /**
     * @param $length
     * @return array
     */
    protected function nextBytes ($length): array
    {
        $data = $this->data->nextBytes($length);
        return $data;
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