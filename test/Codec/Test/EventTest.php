<?php
//
//namespace Codec\Test;
//
//use Codec\Base;
//use Codec\ScaleBytes;
//use Codec\Types\ScaleInstance;
//use PHPUnit\Framework\TestCase;
//
//require_once "const.php";
//
//final class EventTest extends TestCase
//{
//    public function testSampleEventsDecoder ()
//    {
//        $codec = new ScaleInstance(Base::create());
//        $metadataInstant = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV13));
//        $decodeEvent = $codec->process("Vec<EventRecord>",
//            new ScaleBytes("0x080000000000000050e90b0b000000000200000001000000000080b2e60e00000000020000"), $metadataInstant["metadata"]);
//        $this->assertEquals('[{"phase":0,"extrinsic_index":0,"look_up":"0000","module_id":"System","event_id":"ExtrinsicSuccess","params":[{"type":"DispatchInfo","value":{"weight":"185330000","class":"Mandatory","paysFee":"Yes"}}],"topic":[]},{"phase":0,"extrinsic_index":1,"look_up":"0000","module_id":"System","event_id":"ExtrinsicSuccess","params":[{"type":"DispatchInfo","value":{"weight":"250000000","class":"Mandatory","paysFee":"Yes"}}],"topic":[]}]',
//            json_encode($decodeEvent));
//    }
//
//    public function testTransferEventsDecoder ()
//    {
//        $codec = new ScaleInstance(Base::create());
//        $metadataInstant = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV13));
//        $decodeEvent = $codec->process("Vec<EventRecord>",
//            new ScaleBytes("0x1400000000000000a0dc040b00000000020000000100000005028897e4fdc4b935d9afd1440e2705559c46508024357e255c584efde50c5b625507c12e8b63d2592412cbbde38e96181551234bb57ec8438c1281e212b5bed72bbce3e7a5d0010000000000000000000000000100000013060b817c070000000000000000000000000000010000000504e693f8c8c6043a5d8c8ed64d56523d157625011947a8a79881987d9e9100963a4320df01000000000000000000000000000001000000000080e8780a00000000000000"), $metadataInstant["metadata"]);
//
//        $this->assertEquals(["phase" => 0, "extrinsic_index" => 1, "look_up" => "0502", "module_id" => "Balances",
//            "event_id" => "Transfer", "params" => [
//                ["type" => "AccountId", "value" => "8897e4fdc4b935d9afd1440e2705559c46508024357e255c584efde50c5b6255"],
//                ["type" => "AccountId", "value" => "07c12e8b63d2592412cbbde38e96181551234bb57ec8438c1281e212b5bed72b"],
//                ["type" => "Balance", "value" => gmp_init(1995648263100)],
//            ], "topic" => [],
//        ], $decodeEvent[1]);
//    }
//
//    public function testMismatchMetadata ()
//    {
//        $codec = new ScaleInstance(Base::create());
//        $metadataInstant = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV13));
//
//        // kusama block num 8000000 https://kusama.subscan.io/block/8000000
//        // look_up mismatch metadata
//        $this->expectException(\InvalidArgumentException::class);
//        $codec->process("Vec<EventRecord>",
//            new ScaleBytes("0x0c00000000000000480e0d0b0000000002000000010000003500e8030000fcd2d3cdcf7f3a9b646a748eda1b190c8015f735135c76d73c79d4db31098c089e1f7a1c522ab43821f2d09e1552bb0666422c4e3c7c70b71637b1fb0d226b5b93d223baac7b9f128d501b953a16b70d06468320df48373df338e1612b75e29af1cc48e4c4f2c591428afbb2b140671f1aa3ca9a69665e46f6a01cb3bcc3185b7525212ece125f58bbde7f00e101c71a39dfff4c105acc39d76fd5aea3b299f99c666a3b28eb44fcd456cfcd17e5476c5c4b806838308ba38961acea7913cd712a84c34178af4cee16370f87464c17d07a6f9b0ca85c7b4a9ce7a8ae04d94784024671a6e9febeeb46cbeaa0693f4378102503af4d0ab8e8f35a063e7229d38b20162c710c4c02ab787717f755ca3cf3b23d433a9104f711f91ca1960ec8d6bd698680aa5cf9053e6b33439c40ba2be6ffc6f09f09514e762872ad2c820b0a55e902c0a1528e93662ee39bf6a094a6874420c3d7214b1495879cc8628d128c606b190e440a005e4b4f6479202446fd5a0a0486d7afa0c4818ee9011860f906ad13a4d34691b4e258accebb1de9053e2ca47434d00108b7e9ebdbfbef67d4a61aba0523829ab908066175726120734a11080000000005617572610101f2041f571cf3de2701e2681c583f4debd83e0876e5827ae40d3120297d32055b14c0ba7e9cbe54525324066258aecd6c6ad7898d4b05f25cf9698313320c6582000000000b00000000000100000000002039e80e00000000020000"),
//            $metadataInstant["metadata"]);
//    }
//}
