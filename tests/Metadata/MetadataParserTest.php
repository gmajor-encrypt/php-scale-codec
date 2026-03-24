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

    public function testParseMetadataWithTypes(): void
    {
        // v14 metadata with one type (U32 primitive)
        $hex = '0x6d657461' // magic
            . '0e' // version 14
            . '01' // 1 type
            . '00' // path length 0
            . '00' // 0 params
            . '05' // primitive kind
            . '05' // U32 primitive
            . '00' // 0 docs
            . '00' // 0 pallets
            . '04' // extrinsic version
            . '00' // address type
            . '00' // call type
            . '00' // signature type
            . '00'; // extra type

        $metadata = $this->parser->parse($hex);

        $this->assertCount(1, $metadata->getTypes());
        $type = $metadata->getType(0);
        $this->assertInstanceOf(TypeDefinition::class, $type);
        $this->assertTrue($type->isPrimitive());
        $this->assertEquals('U32', $type->getPrimitiveType());
    }

    public function testParseMetadataWithPallet(): void
    {
        // v14 metadata with one pallet
        $hex = '0x6d657461' // magic
            . '0e' // version 14
            . '00' // 0 types
            . '01' // 1 pallet
            . '0b' // name length 11
            . '53797374656d' // "System"
            . '00' // no storage
            . '00' // no calls
            . '00' // no events
            . '00' // 0 constants
            . '00' // no errors
            . '00' // pallet index
            . '04' // extrinsic version
            . '00' // address type
            . '00' // call type
            . '00' // signature type
            . '00'; // extra type

        $metadata = $this->parser->parse($hex);

        $this->assertCount(1, $metadata->getPallets());
        $pallet = $metadata->getPallet('System');
        $this->assertInstanceOf(Pallet::class, $pallet);
        $this->assertEquals('System', $pallet->name);
        $this->assertEquals(0, $pallet->index);
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
}
