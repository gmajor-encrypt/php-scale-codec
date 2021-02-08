<?php

namespace Codec\Types;

use Codec\ScaleBytes;

interface CodecInterface
{
    /**
     * decode
     *
     * @return mixed
     */
    public function decode();

    /**
     * encode
     *
     * @param mixed $param
     * @return mixed
     */
    public function encode($param);
}
