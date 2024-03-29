<?php


namespace Codec\Test;

use Codec\Base;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use PHPUnit\Framework\TestCase;

require_once "const.php";

final class MetadataTest extends TestCase
{
    public function testMetadataV12Decoder ()
    {
        $codec = new ScaleInstance(Base::create());
        $r = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV12));
        $this->assertEquals("12", $r["metadata_version"]);
        $this->assertEquals([
            "CheckSpecVersion",
            "CheckTxVersion",
            "CheckGenesis",
            "CheckMortality",
            "CheckNonce",
            "CheckWeight",
            "ChargeTransactionPayment"
        ], $r["metadata"]["extrinsic"]["signedExtensions"]);
        unset($r["metadata"]["call_index"], $r["metadata"]["event_index"]);

        self::assertEquals($codec->createTypeByTypeString("metadata")->encode($r), Constant::$metadataStaticV12);
    }

    public function testMetadataV13Decoder ()
    {
        $codec = new ScaleInstance(Base::create());
        $r = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV13));
        $this->assertEquals("13", $r["metadata_version"]);
        $this->assertEquals([
            "CheckSpecVersion",
            "CheckTxVersion",
            "CheckGenesis",
            "CheckMortality",
            "CheckNonce",
            "CheckWeight",
            "ChargeTransactionPayment",
            "PrevalidateAttests"
        ], $r["metadata"]["extrinsic"]["signedExtensions"]);

        unset($r["metadata"]["call_index"], $r["metadata"]["event_index"]); // del call_index, event_index just for decode extrinsic or event
        self::assertEquals($codec->createTypeByTypeString("metadata")->encode($r), Constant::$metadataStaticV13);
    }

    public function testMetadataV14Decoder()
    {
        $codec = new ScaleInstance(Base::create());
        $r = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV14));
        $this->assertEquals("14", $r["metadata_version"]);
        self::assertEquals($codec->createTypeByTypeString("metadata")->encode($r), Constant::$metadataStaticV14);
    }
}