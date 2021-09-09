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
        ], $r["extrinsic"]["signedExtensions"]);
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
        ], $r["extrinsic"]["signedExtensions"]);
    }
}