<?php


namespace Codec\Test;

use Codec\Base;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use PHPUnit\Framework\TestCase;

require "const.php";

final class MetadataTest extends TestCase
{
    public function testMetadataDecoder ()
    {
        $codec = new ScaleInstance(Base::create());
        $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV12));
    }
}