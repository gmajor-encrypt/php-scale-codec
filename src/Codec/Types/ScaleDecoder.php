<?php

namespace Codec\Types;

use Codec\Generator;
use Codec\ScaleBytes;
use Codec\Utiles;

class ScaleDecoder implements CodecInterface
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
    protected $typeString;

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
     * @var $metadata
     */
    protected $metadata;

    /**
     * @var string $rawData
     */
    protected $rawData;

    /**
     * ScaleDecoder constructor.
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param ScaleBytes $data
     * @param string $subType
     * @param $metadata
     */
    public function init(ScaleBytes $data, string $subType = "", $metadata = null)
    {
        $this->data = $data;
        $this->subType = $subType;
        $this->metadata = $metadata;
    }

    /**
     * buildStructMapping
     */
    protected function buildStructMapping()
    {

    }


    /**
     * @param string $typeString
     */
    protected function process(string $typeString)
    {

    }


    /**
     * createTypeByTypeString
     *
     * @param string $typeString
     * @param array $arg
     * @return mixed
     */
    protected function createTypeByTypeString(string $typeString, ...$arg)
    {
        $typeString = self::convertType($typeString);
        $match = array();

        if ($typeString[-1] == '>') {
            $codecInstant = $this->generator->getRegistry(strtolower($typeString));
            if (!is_null($codecInstant)) {
                $codecInstant->init($this->data);
                return $codecInstant;
            }
            preg_match("/^([^<]*)<(.+)>$/", $typeString, $match);
        }

        if (count($match) > 0) {
            $codecInstant = $this->generator->getRegistry(strtolower($match[0]));
            if (!is_null($codecInstant)) {
                $codecInstant->init($this->data);
                return $codecInstant;
            }
        } else {
            $codecInstant = $this->generator->getRegistry(strtolower($typeString));
            if (!is_null($codecInstant)) {
                $codecInstant->init($this->data);
                return $codecInstant;
            }
        }

        if ($typeString[0] == '(' && $typeString[-1] == ')') {
            $struct = $this->generator->getRegistry('struct');
            $struct->typeString = $typeString;
            $struct->buildStructMapping();
        }


        throw new \InvalidArgumentException(sprintf('Unknown codec type "%s"', $typeString));
    }


    /**
     * @param $length
     * @return array
     */
    protected function nextBytes($length)
    {
        $data = $this->data->nextBytes($length);
        $this->rawData = $this->rawData . (Utiles::bytesToHex($data));
        return $data;
    }

    /**
     * nextU8
     * @return int
     */
    protected function nextU8()
    {
        $data = $this->nextBytes(1);
        return unpack("C", Utiles::bytesToHex($data))[1];
    }

    /**
     * nextBool
     * @return bool
     */
    protected function nextBool()
    {
        $data = $this->nextBytes(1);
        if (!in_array($data[0], [0, 1])) {
            throw new \UnexpectedValueException(sprintf('InValid value  "%s" type bool', $data));
        }
        return $data[0] === 1;
    }


    /**
     * convertType
     *
     * @param string $typeString
     * @return string
     */
    private function convertType(string $typeString)
    {
        if ($typeString == '()') {
            return "Null";
        }
        return $typeString;
    }


    public function decode()
    {
        // TODO: Implement decode() method.
    }

    public function encode()
    {
        // TODO: Implement encode() method.
    }
}