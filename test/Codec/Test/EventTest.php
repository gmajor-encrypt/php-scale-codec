<?php

namespace Codec\Test;

use Codec\Base;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use PHPUnit\Framework\TestCase;

require_once "const.php";

final class EventTest extends TestCase
{
    public function testSampleEventsDecoder ()
    {
        $codec = new ScaleInstance(Base::create());
        $metadataInstant = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV13));
        $decodeEvent = $codec->process("Vec<EventRecord>",
            new ScaleBytes("0x080000000000000050e90b0b000000000200000001000000000080b2e60e00000000020000"), $metadataInstant);
        $this->assertEquals('[{"phase":0,"extrinsic_index":0,"look_up":"0000","module_id":"System","event_id":"ExtrinsicSuccess","params":[{"type":"DispatchInfo","value":{"weight":"185330000","class":"Mandatory","paysFee":"Yes"}}],"topic":[]},{"phase":0,"extrinsic_index":1,"look_up":"0000","module_id":"System","event_id":"ExtrinsicSuccess","params":[{"type":"DispatchInfo","value":{"weight":"250000000","class":"Mandatory","paysFee":"Yes"}}],"topic":[]}]',
            json_encode($decodeEvent));
    }
}
