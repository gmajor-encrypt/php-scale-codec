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
    public function addScaleType (string $scaleType, $provider)
    {
        $this->scale_type[strtolower($scaleType)] = $provider;
    }

    /**
     * @return array
     */
    public function getScaleType (): array
    {
        return $this->scale_type;
    }

    /**
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