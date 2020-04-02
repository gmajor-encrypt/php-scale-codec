<?php

namespace Codec\Types;

use Codec\ScaleBytes;

interface CodecInterface
{
    public function decode();

    public function encode();
}
