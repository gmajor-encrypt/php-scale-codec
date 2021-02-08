<?php

namespace Codec;


class Generator
{

    protected $scale_type = array();

    protected $registers = array();

    /**
     * @param string $scaleType
     * @param $provider
     */
    public function addScaleType(string $scaleType, $provider)
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
    public function __call(string $method, array $attributes)
    {
        $instant = self::getRegistry($method);
        if($instant == null) {
            return null;
        }
        if(count($attributes)>0){
            $instant->init($attributes[0]);
        }
        return $instant;
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function getRegistry(string $type)
    {
        if (isset($this->scale_type[strtolower($type)])) {
            return $this->scale_type[strtolower($type)];
        }
        return null;
    }

}