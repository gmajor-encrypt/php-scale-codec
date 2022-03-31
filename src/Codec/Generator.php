<?php

namespace Codec;


class Generator
{

    protected array $scale_type = array();

    protected array $registers = array();

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
     * get one scaleType from Registry
     * return null if not found
     *
     * @param string $type
     * @return mixed
     */
    public function getRegistry (string $type): mixed
    {
        if (isset($this->scale_type[strtolower($type)])) {
            return $this->scale_type[strtolower($type)];
        }
        return null;
    }

}