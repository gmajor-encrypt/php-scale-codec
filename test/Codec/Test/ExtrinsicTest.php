<?php

namespace Codec\Test;

use Codec\Base;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use PHPUnit\Framework\TestCase;

require_once "const.php";

final class ExtrinsicTest extends TestCase
{
    public function testSampleExtrinsicDecoder ()
    {
        $codec = new ScaleInstance(Base::create());
        $metadataInstant = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV13));
        $decodeEvent = $codec->process("Extrinsic", new ScaleBytes("0x280403000b819fc2837a01"), $metadataInstant);
        $this->assertEquals([
            'extrinsic_length' => 10,
            'version' => '04',
            'look_up' => '0300',
            'module_id' => 'Timestamp',
            'call_name' => 'set',
            'params' => [["name" => "now", "type" => "Compact<T::Moment>", "value" => gmp_init(1625708208001)]],
        ], $decodeEvent);
    }
}
