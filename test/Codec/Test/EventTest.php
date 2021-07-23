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

    public function testTransferEventsDecoder ()
    {
        $codec = new ScaleInstance(Base::create());
        $metadataInstant = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV13));
        $decodeEvent = $codec->process("Vec<EventRecord>",
            new ScaleBytes("0x1400000000000000a0dc040b00000000020000000100000005028897e4fdc4b935d9afd1440e2705559c46508024357e255c584efde50c5b625507c12e8b63d2592412cbbde38e96181551234bb57ec8438c1281e212b5bed72bbce3e7a5d0010000000000000000000000000100000013060b817c070000000000000000000000000000010000000504e693f8c8c6043a5d8c8ed64d56523d157625011947a8a79881987d9e9100963a4320df01000000000000000000000000000001000000000080e8780a00000000000000"), $metadataInstant);

        $this->assertEquals(["phase" => 0, "extrinsic_index" => 1, "look_up" => "0502", "module_id" => "Balances",
            "event_id" => "Transfer", "params" => [
                ["type" => "AccountId", "value" => "8897e4fdc4b935d9afd1440e2705559c46508024357e255c584efde50c5b6255"],
                ["type" => "AccountId", "value" => "07c12e8b63d2592412cbbde38e96181551234bb57ec8438c1281e212b5bed72b"],
                ["type" => "Balance", "value" => gmp_init(1995648263100)],
            ], "topic" => [],
        ], $decodeEvent[1]);
    }
}
