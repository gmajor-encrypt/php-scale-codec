<?php

namespace Codec\Types;

use Codec\Types\ScaleDecoder;
use Codec\Utils;

//Todo
class TInt extends ScaleDecoder
{

    /**
     * @var integer $Width
     *
     */
    protected $Width;

    function decode ()
    {

    }

    function encode ($param)
    {
        return parent::encode($param); // TODO: Change the autogenerated stub
    }

}