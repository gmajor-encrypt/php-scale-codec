<?php

namespace Codec;


class Generator
{

    protected $scale_type = array();

    protected $registers = array();

    /**
     * add Scale type to scale_type Registry
     *
     * @param string $scaleType
     * @param $provider
     */
    public function addScaleType (string $scaleType, $provider)
    {
        $this->scale_type[strtolower($scaleType)] = $provider;
    }

    /**
     * get all scaleType Registry
     *
     * @return array
     */
    public function getScaleType (): array
    {
//        print_r($this->scale_type["AccountIndex"]);
        return $this->scale_type;
    }

    /**
     * get one scaleType from Registry
     * return null if not found
     *
     * @param string $type
     * @return mixed
     */
    public function getRegistry (string $type)
    {
        if (isset($this->scale_type[strtolower($type)])) {
            return $this->scale_type[strtolower($type)];
        }
        return null;
    }

}