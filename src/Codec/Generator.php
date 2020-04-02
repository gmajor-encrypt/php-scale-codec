<?php

namespace Codec;


class Generator
{

    protected $scale_type = array();

    protected $registers = array();

    /**
     * @param $provider
     * @param string $scaleType
     */
    public function addScaleType($scaleType, $provider)
    {
        $this->scale_type[strtolower($scaleType)] = $provider;
    }

    /**
     * @return array
     */
    public function getScaleType()
    {
        return $this->scale_type;
    }


    public function __destruct()
    {

    }

    /**
     * @param string $method
     * @param array $attributes
     *
     * @return mixed
     */
    public function __call($method, $attributes)
    {
        if (count($attributes) !== 1) {
            return new \InvalidArgumentException(sprintf('InvalidArgumentException "%s"', $method));
        }
        $instant = self::getRegistry($method);
        if($instant == null) {
            return null;
        }
        $instant->init($attributes[0]);
        return $instant;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getRegistry($type)
    {
        if (isset($this->scale_type[strtolower($type)])) {
            return $this->scale_type[strtolower($type)];
        }
        return null;
    }

}