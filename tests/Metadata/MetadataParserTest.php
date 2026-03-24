<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Metadata\{Metadata, MetadataParser, MetadataVersion, Pallet, TypeDefinition};

class MetadataParserTest extends TestCase
{
    private MetadataParser $parser;

    protected function setUp(): void
    {
        $this->parser = new MetadataParser();
    }

    public function testParseMetadataVersion14(): void
    {
        // Minimal v14 metadata
        // magic "meta" + version 14 + types(0) + pallets(0) + extrinsic
        $hex = '0x6d657461' // magic "meta" in little-endian
            . '0e' // version 14
            . '00' // 0 types
            . '00' // 0 pallets
            . '04' // extrinsic version 4
            . '00' // address type
            . '00' // call type
            . '00' // signature type
            . '00'; // extra type

        $metadata = $this->parser->parse($hex);

        $this->assertEquals(MetadataVersion::V14, $metadata->version);
        $this->assertEmpty($metadata->getTypes());
        $this->assertEmpty($metadata->getPallets());
    }

    public function testParseMetadataVersion15(): void
    {
        // Minimal v15 metadata
        $hex = '0x6d657461' // magic "meta"
            . '0f' // version 15
            . '00' // 0 types
            . '00' // 0 pallets
            . '04' // extrinsic version
            . '00' // address type
            . '00' // call type
            . '00' // signature type
            . '00' // extra type
            . '00'; // 0 APIs

        $metadata = $this->parser->parse($hex);

        $this->assertEquals(MetadataVersion::V15, $metadata->version);
    }

    public function testMetadataCaching(): void
    {
        $hex = '0x6d6574610e0000000400000000';

        // First parse
        $metadata1 = $this->parser->parse($hex);

        // Second parse (should be cached)
        $metadata2 = $this->parser->parse($hex);

        // Should be same instance (cached)
        $this->assertSame($metadata1, $metadata2);

        // Clear cache and parse again
        MetadataParser::clearCache();
        $metadata3 = $this->parser->parse($hex);

        // Should be different instance after cache clear
        $this->assertNotSame($metadata1, $metadata3);
    }

    public function testMetadataVersionSupport(): void
    {
        $v14 = MetadataVersion::V14;
        $this->assertTrue($v14->supportsPortableTypes());
        $this->assertFalse($v14->supportsApis());

        $v15 = MetadataVersion::V15;
        $this->assertTrue($v15->supportsPortableTypes());
        $this->assertTrue($v15->supportsApis());
    }

    public function testMetadataVersionFromInt(): void
    {
        $this->assertEquals(MetadataVersion::V12, MetadataVersion::fromInt(12));
        $this->assertEquals(MetadataVersion::V13, MetadataVersion::fromInt(13));
        $this->assertEquals(MetadataVersion::V14, MetadataVersion::fromInt(14));
        $this->assertEquals(MetadataVersion::V15, MetadataVersion::fromInt(15));
        $this->assertNull(MetadataVersion::fromInt(99));
    }

    public function testInvalidMagicNumber(): void
    {
        $this->expectException(\Substrate\ScaleCodec\Exception\ScaleDecodeException::class);
        $this->expectExceptionMessage('Invalid metadata magic number');

        $hex = '0x00000000' . '0e' . '00'; // Wrong magic
        $this->parser->parse($hex);
    }

    public function testUnsupportedVersion(): void
    {
        $this->expectException(\Substrate\ScaleCodec\Exception\ScaleDecodeException::class);
        $this->expectExceptionMessage('Unsupported metadata version');

        $hex = '0x6d657461' . 'ff' . '00'; // Version 255
        $this->parser->parse($hex);
    }

    public function testTypeDefinitionMethods(): void
    {
        // Composite type (struct)
        $compositeDef = new TypeDefinition(
            id: 0,
            path: 'Test::Struct',
            def: ['composite' => ['fields' => []]]
        );

        $this->assertTrue($compositeDef->isComposite());
        $this->assertFalse($compositeDef->isVariant());
        $this->assertEquals('composite', $compositeDef->getKind());
        $this->assertEquals([], $compositeDef->getFields());

        // Variant type (enum)
        $variantDef = new TypeDefinition(
            id: 1,
            path: 'Test::Enum',
            def: ['variant' => ['variants' => [['name' => 'A', 'index' => 0]]]]
        );

        $this->assertTrue($variantDef->isVariant());
        $this->assertEquals('variant', $variantDef->getKind());
        $this->assertCount(1, $variantDef->getVariants());

        // Sequence type
        $sequenceDef = new TypeDefinition(
            id: 2,
            def: ['sequence' => ['type' => 0]]
        );

        $this->assertTrue($sequenceDef->isSequence());
        $this->assertEquals(0, $sequenceDef->getElementType());

        // Array type
        $arrayDef = new TypeDefinition(
            id: 3,
            def: ['array' => ['type' => 0, 'len' => 32]]
        );

        $this->assertTrue($arrayDef->isArray());
        $arrInfo = $arrayDef->getArrayInfo();
        $this->assertEquals(0, $arrInfo['type']);
        $this->assertEquals(32, $arrInfo['len']);

        // Tuple type
        $tupleDef = new TypeDefinition(
            id: 4,
            def: ['tuple' => [0, 1, 2]]
        );

        $this->assertTrue($tupleDef->isTuple());
        $this->assertEquals([0, 1, 2], $tupleDef->getTupleTypes());

        // Primitive type
        $primitiveDef = new TypeDefinition(
            id: 5,
            def: ['primitive' => 'U32']
        );

        $this->assertTrue($primitiveDef->isPrimitive());
        $this->assertEquals('U32', $primitiveDef->getPrimitiveType());

        // Compact type
        $compactDef = new TypeDefinition(
            id: 6,
            def: ['compact' => ['type' => 0]]
        );

        $this->assertTrue($compactDef->isCompact());

        // BitSequence type
        $bitseqDef = new TypeDefinition(
            id: 7,
            def: ['bitsequence' => true]
        );

        $this->assertTrue($bitseqDef->isBitSequence());
    }

    public function testPalletMethods(): void
    {
        $pallet = new Pallet(
            name: 'System',
            index: 0,
            storage: [['name' => 'Account'], ['name' => 'BlockHash']],
            calls: [['name' => 'remark'], ['name' => 'setHeapPages']],
            events: [['name' => 'ExtrinsicSuccess'], ['name' => 'ExtrinsicFailed']],
            errors: [['name' => 'BadOrigin']],
            constants: [['name' => 'BlockHashCount']],
        );

        $this->assertEquals('System', $pallet->name);
        $this->assertEquals(0, $pallet->index);

        // Test storage lookup
        $this->assertNotNull($pallet->getStorage('Account'));
        $this->assertNotNull($pallet->getStorage('BlockHash'));
        $this->assertNull($pallet->getStorage('NonExistent'));

        // Test call lookup
        $this->assertNotNull($pallet->getCall('remark'));
        $this->assertNull($pallet->getCall('nonExistent'));

        // Test event lookup
        $this->assertNotNull($pallet->getEvent(0));
        $this->assertNull($pallet->getEvent(99));

        // Test error lookup
        $this->assertNotNull($pallet->getError(0));
        $this->assertNull($pallet->getError(99));

        // Test constant lookup
        $this->assertNotNull($pallet->getConstant('BlockHashCount'));
        $this->assertNull($pallet->getConstant('NonExistent'));
    }

    public function testMetadataGetTypeByName(): void
    {
        $metadata = new Metadata(MetadataVersion::V14);
        
        $type = new TypeDefinition(
            id: 0,
            path: 'frame_system::AccountInfo',
            def: ['composite' => ['fields' => []]]
        );
        $metadata->addType($type);

        // Look up by full path
        $this->assertEquals(0, $metadata->getTypeIdByName('frame_system::AccountInfo'));

        // Look up by last segment
        $this->assertEquals(0, $metadata->getTypeIdByName('AccountInfo'));

        // Non-existent type
        $this->assertNull($metadata->getTypeIdByName('NonExistent'));
    }

    public function testMetadataGetExtrinsicInfo(): void
    {
        $metadata = new Metadata(
            version: MetadataVersion::V14,
            extrinsic: [
                'version' => 4,
                'addressType' => 0,
                'callType' => 1,
                'signatureType' => 2,
                'extraType' => 3,
            ]
        );

        $this->assertEquals(4, $metadata->getExtrinsicVersion());
        $this->assertEquals(0, $metadata->getExtrinsicAddressType());
        $this->assertEquals(1, $metadata->getExtrinsicCallType());
        $this->assertEquals(2, $metadata->getExtrinsicSignatureType());
        $this->assertEquals(3, $metadata->getExtrinsicExtraType());
    }

    public function testMetadataPalletLookup(): void
    {
        $metadata = new Metadata(MetadataVersion::V14);
        
        $pallet1 = new Pallet(name: 'System', index: 0);
        $pallet2 = new Pallet(name: 'Balances', index: 1);
        
        $metadata->addPallet($pallet1);
        $metadata->addPallet($pallet2);

        // Lookup by name
        $this->assertSame($pallet1, $metadata->getPallet('System'));
        $this->assertSame($pallet2, $metadata->getPallet('Balances'));
        $this->assertNull($metadata->getPallet('NonExistent'));

        // Lookup by index
        $this->assertSame($pallet1, $metadata->getPalletByIndex(0));
        $this->assertSame($pallet2, $metadata->getPalletByIndex(1));
        $this->assertNull($metadata->getPalletByIndex(99));
    }
}
