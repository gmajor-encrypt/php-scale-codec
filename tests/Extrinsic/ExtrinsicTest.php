<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Extrinsic;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Extrinsic\{Extrinsic, ExtrinsicBuilder, ExtrinsicEncoder, ExtrinsicDecoder, Signature};

class ExtrinsicTest extends TestCase
{
    private ExtrinsicEncoder $encoder;
    private ExtrinsicDecoder $decoder;

    protected function setUp(): void
    {
        $this->encoder = new ExtrinsicEncoder();
        $this->decoder = new ExtrinsicDecoder();
    }

    // ==================== Signature Tests ====================

    public function testSignatureCreation(): void
    {
        $signature = new Signature(
            signer: '0x' . str_repeat('01', 32),
            signature: '0x' . str_repeat('aa', 64),
            extra: ['nonce' => 5, 'tip' => 100],
            signerType: 'sr25519'
        );

        $this->assertEquals('0x' . str_repeat('01', 32), $signature->signer);
        $this->assertEquals('0x' . str_repeat('aa', 64), $signature->signature);
        $this->assertEquals('sr25519', $signature->signerType);
        $this->assertTrue($signature->isSr25519());
        $this->assertFalse($signature->isEd25519());
        $this->assertFalse($signature->isEcdsa());
    }

    public function testSignatureEd25519(): void
    {
        $signature = new Signature(
            signer: '0x' . str_repeat('01', 32),
            signature: '0x' . str_repeat('bb', 64),
            signerType: 'ed25519'
        );

        $this->assertTrue($signature->isEd25519());
        $this->assertFalse($signature->isSr25519());
    }

    public function testSignatureEcdsa(): void
    {
        $signature = new Signature(
            signer: '0x' . str_repeat('01', 32),
            signature: '0x' . str_repeat('cc', 65),
            signerType: 'ecdsa'
        );

        $this->assertTrue($signature->isEcdsa());
        $this->assertFalse($signature->isSr25519());
    }

    public function testSignatureGetters(): void
    {
        $signature = new Signature(
            signer: '0x' . str_repeat('01', 32),
            signature: '0x' . str_repeat('aa', 64),
            extra: [
                'era' => ['type' => 'mortal', 'phase' => 100],
                'nonce' => 42,
                'tip' => 1000
            ]
        );

        $this->assertEquals(['type' => 'mortal', 'phase' => 100], $signature->getEra());
        $this->assertEquals(42, $signature->getNonce());
        $this->assertEquals(1000, $signature->getTip());
    }

    // ==================== Extrinsic Tests ====================

    public function testExtrinsicUnsigned(): void
    {
        $extrinsic = new Extrinsic(
            call: ['pallet' => 'System', 'function' => 'remark', 'args' => []],
            signature: null
        );

        $this->assertFalse($extrinsic->isSigned());
        $this->assertEquals('System', $extrinsic->getPallet());
        $this->assertEquals('remark', $extrinsic->getFunction());
        $this->assertNull($extrinsic->getSigner());
    }

    public function testExtrinsicSigned(): void
    {
        $signature = new Signature(
            signer: '0x' . str_repeat('01', 32),
            signature: '0x' . str_repeat('aa', 64)
        );

        $extrinsic = new Extrinsic(
            call: ['pallet' => 'Balances', 'function' => 'transfer', 'args' => []],
            signature: $signature
        );

        $this->assertTrue($extrinsic->isSigned());
        $this->assertEquals('Balances', $extrinsic->getPallet());
        $this->assertEquals('transfer', $extrinsic->getFunction());
        $this->assertEquals('0x' . str_repeat('01', 32), $extrinsic->getSigner());
    }

    public function testExtrinsicGetters(): void
    {
        $signature = new Signature(
            signer: '0x' . str_repeat('01', 32),
            signature: '0x' . str_repeat('aa', 64),
            extra: ['nonce' => 10, 'tip' => 500]
        );

        $extrinsic = new Extrinsic(
            call: ['pallet' => 'Test', 'function' => 'test', 'args' => ['a' => 1]],
            signature: $signature,
            extra: ['era' => ['type' => 'immortal']]
        );

        $this->assertEquals(['a' => 1], $extrinsic->getArguments());
        $this->assertEquals(10, $extrinsic->getNonce());
        $this->assertEquals(500, $extrinsic->getTip());
        $this->assertEquals(['type' => 'immortal'], $extrinsic->getEra());
    }

    // ==================== ExtrinsicBuilder Tests ====================

    public function testBuilderUnsignedExtrinsic(): void
    {
        $extrinsic = ExtrinsicBuilder::create()
            ->pallet('System')
            ->function('remark')
            ->palletIndex(0)
            ->functionIndex(0)
            ->args([0x00])
            ->buildUnsigned();

        $this->assertFalse($extrinsic->isSigned());
        $this->assertEquals('System', $extrinsic->getPallet());
        $this->assertEquals('remark', $extrinsic->getFunction());
    }

    public function testBuilderSignedExtrinsic(): void
    {
        $extrinsic = ExtrinsicBuilder::create()
            ->pallet('Balances')
            ->function('transfer')
            ->palletIndex(5)
            ->functionIndex(0)
            ->signer('0x' . str_repeat('01', 32))
            ->signature('0x' . str_repeat('aa', 64))
            ->nonce(1)
            ->tip(0)
            ->immortal()
            ->build();

        $this->assertTrue($extrinsic->isSigned());
        $this->assertEquals('Balances', $extrinsic->getPallet());
        $this->assertEquals('transfer', $extrinsic->getFunction());
        $this->assertEquals(1, $extrinsic->getNonce());
    }

    public function testBuilderMortalEra(): void
    {
        $extrinsic = ExtrinsicBuilder::create()
            ->pallet('System')
            ->function('remark')
            ->signer('0x' . str_repeat('01', 32))
            ->signature('0x' . str_repeat('aa', 64))
            ->mortal(64, 100)
            ->build();

        $this->assertNotNull($extrinsic->getEra());
        $this->assertEquals(64, $extrinsic->getEra()['period']);
        $this->assertEquals(100, $extrinsic->getEra()['phase']);
    }

    public function testBuilderAddArg(): void
    {
        $extrinsic = ExtrinsicBuilder::create()
            ->pallet('Balances')
            ->function('transfer')
            ->arg('dest', '0x' . str_repeat('ff', 32))
            ->arg('value', 1000)
            ->buildUnsigned();

        $args = $extrinsic->getArguments();
        $this->assertEquals('0x' . str_repeat('ff', 32), $args['dest']);
        $this->assertEquals(1000, $args['value']);
    }

    // ==================== Encoder/Decoder Round-Trip Tests ====================

    public function testEncodeUnsignedExtrinsic(): void
    {
        $extrinsic = ExtrinsicBuilder::create()
            ->pallet('System')
            ->function('remark')
            ->palletIndex(0)
            ->functionIndex(0)
            ->buildUnsigned();

        $encoded = $this->encoder->encode($extrinsic);

        $this->assertNotEmpty($encoded->toHex());
    }

    public function testEncodeSignedExtrinsic(): void
    {
        $extrinsic = ExtrinsicBuilder::create()
            ->pallet('System')
            ->function('remark')
            ->palletIndex(0)
            ->functionIndex(0)
            ->signer('0x' . str_repeat('01', 32))
            ->signature('0x' . str_repeat('aa', 64))
            ->nonce(0)
            ->tip(0)
            ->immortal()
            ->build();

        $encoded = $this->encoder->encode($extrinsic);

        $this->assertNotEmpty($encoded->toHex());
    }

    public function testDecodeExtrinsic(): void
    {
        // Create a simple encoded extrinsic manually
        // Length (1 byte) + version (1 byte) + call
        $hex = '0x' // prefix
            . '04'   // length = 4
            . '04'   // version 4, unsigned
            . '00'   // pallet index 0
            . '00';  // function index 0

        $extrinsic = $this->decoder->decodeHex($hex);

        $this->assertFalse($extrinsic->isSigned());
        $this->assertEquals(0, $extrinsic->call['palletIndex']);
        $this->assertEquals(0, $extrinsic->call['functionIndex']);
    }

    public function testDecodeSignedExtrinsic(): void
    {
        // Minimal signed extrinsic
        // This is a simplified test - real extrinsics would have proper signatures
        $hex = '0x'
            . 'a4'   // length (approximate)
            . '84'   // version 4, signed (0x80 | 4)
            . '00'   // MultiAddress::Id
            . str_repeat('01', 32) // signer (32 bytes)
            . str_repeat('aa', 64) // signature (64 bytes)
            . '00'   // era (immortal)
            . '00'   // nonce (compact 0)
            . '00'   // tip (compact 0)
            . '00'   // pallet index
            . '00';  // function index

        $extrinsic = $this->decoder->decodeHex($hex);

        $this->assertTrue($extrinsic->isSigned());
        $this->assertEquals('0x' . str_repeat('01', 32), $extrinsic->getSigner());
    }

    // ==================== Version Tests ====================

    public function testExtrinsicVersion(): void
    {
        $builder = ExtrinsicBuilder::create()
            ->pallet('System')
            ->function('remark')
            ->version(4);

        $this->assertInstanceOf(ExtrinsicBuilder::class, $builder);
    }
}
