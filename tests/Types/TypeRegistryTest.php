<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Types;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Types\{TypeRegistry, TypeFactory, VecType, OptionType, U8, U16, U32, BoolType};
use Substrate\ScaleCodec\Exception\InvalidTypeException;

class TypeRegistryTest extends TestCase
{
    private TypeRegistry $registry;
    private TypeFactory $factory;

    protected function setUp(): void
    {
        $this->registry = new TypeRegistry();
        $this->factory = new TypeFactory($this->registry);
    }

    // ==================== Basic Registration Tests ====================

    public function testRegisterType(): void
    {
        $customType = new U8($this->registry);
        $this->registry->register('CustomU8', $customType);

        $this->assertTrue($this->registry->has('CustomU8'));
        $this->assertInstanceOf(U8::class, $this->registry->get('CustomU8'));
    }

    public function testRegisterTypeWithOverride(): void
    {
        $type1 = new U8($this->registry);
        $type2 = new U32($this->registry);

        $this->registry->register('MyType', $type1);
        $this->assertInstanceOf(U8::class, $this->registry->get('MyType'));

        // Override without flag should throw
        $this->expectException(InvalidTypeException::class);
        $this->registry->register('MyType', $type2);
    }

    public function testRegisterTypeWithOverrideAllowed(): void
    {
        $type1 = new U8($this->registry);
        $type2 = new U32($this->registry);

        $this->registry->register('MyType', $type1);
        $this->registry->register('MyType', $type2, true);

        $this->assertInstanceOf(U32::class, $this->registry->get('MyType'));
    }

    public function testRegisterManyTypes(): void
    {
        $types = [
            'Type1' => new U8($this->registry),
            'Type2' => new U16($this->registry),
            'Type3' => new U32($this->registry),
        ];

        $this->registry->registerMany($types);

        $this->assertTrue($this->registry->has('Type1'));
        $this->assertTrue($this->registry->has('Type2'));
        $this->assertTrue($this->registry->has('Type3'));
    }

    // ==================== Alias Tests ====================

    public function testRegisterAlias(): void
    {
        $this->registry->registerAlias('MyAlias', 'U8');

        $this->assertTrue($this->registry->has('MyAlias'));
        $this->assertInstanceOf(U8::class, $this->registry->get('MyAlias'));
    }

    public function testRegisterMultipleAliases(): void
    {
        $aliases = [
            'UInt8' => 'U8',
            'UInt16' => 'U16',
            'UInt32' => 'U32',
        ];

        $this->registry->registerAliases($aliases);

        $this->assertTrue($this->registry->has('UInt8'));
        $this->assertTrue($this->registry->has('UInt16'));
        $this->assertTrue($this->registry->has('UInt32'));
    }

    public function testAliasChain(): void
    {
        $this->registry->registerAlias('Alias1', 'U8');
        $this->registry->registerAlias('Alias2', 'Alias1');

        $this->assertInstanceOf(U8::class, $this->registry->get('Alias2'));
    }

    public function testCircularAliasDetection(): void
    {
        $this->registry->registerAlias('A', 'B');
        $this->registry->registerAlias('B', 'C');
        $this->registry->registerAlias('C', 'A');

        $this->expectException(InvalidTypeException::class);
        $this->registry->get('A');
    }

    // ==================== Frozen Registry Tests ====================

    public function testFreezeRegistry(): void
    {
        $this->registry->freeze();

        $this->assertTrue($this->registry->isFrozen());

        $this->expectException(InvalidTypeException::class);
        $this->registry->register('NewType', new U8($this->registry));
    }

    public function testUnfreezeRegistry(): void
    {
        $this->registry->freeze();
        $this->assertTrue($this->registry->isFrozen());

        $this->registry->unfreeze();
        $this->assertFalse($this->registry->isFrozen());

        // Should not throw after unfreeze
        $this->registry->register('NewType', new U8($this->registry));
        $this->assertTrue($this->registry->has('NewType'));
    }

    // ==================== Type Factory Tests ====================

    public function testFactoryCreateSimpleType(): void
    {
        $type = $this->factory->create('U8');
        $this->assertInstanceOf(U8::class, $type);
    }

    public function testFactoryCreateVecType(): void
    {
        $type = $this->factory->create('Vec<U8>');
        $this->assertInstanceOf(VecType::class, $type);
    }

    public function testFactoryCreateOptionType(): void
    {
        $type = $this->factory->create('Option<U32>');
        $this->assertInstanceOf(OptionType::class, $type);
    }

    public function testFactoryCreateTupleType(): void
    {
        $type = $this->factory->create('(U8, U32, Bool)');
        $this->assertInstanceOf(\Substrate\ScaleCodec\Types\TupleType::class, $type);
    }

    public function testFactoryCreateFixedArray(): void
    {
        $type = $this->factory->create('[U8; 32]');
        $this->assertInstanceOf(\Substrate\ScaleCodec\Types\FixedArrayType::class, $type);
    }

    public function testFactoryCreateNestedTypes(): void
    {
        $type = $this->factory->create('Vec<Vec<U8>>');
        $this->assertInstanceOf(VecType::class, $type);

        $type = $this->factory->create('Option<Vec<U32>>');
        $this->assertInstanceOf(OptionType::class, $type);
    }

    public function testFactoryCreateEmptyTuple(): void
    {
        $type = $this->factory->create('()');
        $this->assertInstanceOf(\Substrate\ScaleCodec\Types\NullType::class, $type);
    }

    public function testFactoryInvalidType(): void
    {
        $this->expectException(InvalidTypeException::class);
        $this->factory->create('NonExistentType');
    }

    // ==================== Caching Tests ====================

    public function testTypeCaching(): void
    {
        // Get the same type twice
        $type1 = $this->registry->get('U8');
        $type2 = $this->registry->get('U8');

        // They should be different instances (clones)
        $this->assertNotSame($type1, $type2);
    }

    public function testClearCache(): void
    {
        // Access a type to cache it
        $this->registry->get('U8');

        // Clear cache
        $this->registry->clearCache();

        // Should still work after clear
        $type = $this->registry->get('U8');
        $this->assertInstanceOf(U8::class, $type);
    }

    // ==================== Metadata Registration Tests ====================

    public function testRegisterFromMetadataStruct(): void
    {
        $metadata = [
            'MyStruct' => [
                'type' => 'struct',
                'fields' => [
                    ['name' => 'a', 'type' => 'U8'],
                    ['name' => 'b', 'type' => 'U32'],
                ],
            ],
        ];

        $this->registry->registerFromMetadata($metadata);

        $this->assertTrue($this->registry->has('MyStruct'));
        $type = $this->registry->get('MyStruct');
        $this->assertInstanceOf(\Substrate\ScaleCodec\Types\StructType::class, $type);
    }

    public function testRegisterFromMetadataEnum(): void
    {
        $metadata = [
            'MyEnum' => [
                'type' => 'enum',
                'variants' => [
                    ['name' => 'A', 'index' => 0],
                    ['name' => 'B', 'index' => 1],
                ],
            ],
        ];

        $this->registry->registerFromMetadata($metadata);

        $this->assertTrue($this->registry->has('MyEnum'));
        $type = $this->registry->get('MyEnum');
        $this->assertInstanceOf(\Substrate\ScaleCodec\Types\EnumType::class, $type);
    }

    public function testRegisterFromMetadataVec(): void
    {
        $metadata = [
            'MyVec' => [
                'type' => 'sequence',
                'elementType' => 'U8',
            ],
        ];

        $this->registry->registerFromMetadata($metadata);

        $this->assertTrue($this->registry->has('MyVec'));
        $type = $this->registry->get('MyVec');
        $this->assertInstanceOf(VecType::class, $type);
    }

    // ==================== Clone Tests ====================

    public function testCloneRegistry(): void
    {
        $this->registry->register('CustomType', new U8($this->registry));

        $clone = $this->registry->clone();

        $this->assertTrue($clone->has('CustomType'));
        $this->assertNotSame($this->registry, $clone);
    }

    // ==================== Get Registered Types Tests ====================

    public function testGetRegisteredTypes(): void
    {
        $types = $this->registry->getRegisteredTypes();

        $this->assertContains('u8', $types);
        $this->assertContains('u32', $types);
        $this->assertContains('bool', $types);
        $this->assertContains('vec', $types);
        $this->assertContains('option', $types);
    }

    public function testGetAliases(): void
    {
        $this->registry->registerAlias('MyAlias', 'U8');
        $aliases = $this->registry->getAliases();

        $this->assertArrayHasKey('myalias', $aliases);
        $this->assertEquals('u8', $aliases['myalias']);
    }

    // ==================== Callable Factory Tests ====================

    public function testRegisterCallableFactory(): void
    {
        $this->registry->register('LazyType', function () {
            return new U8($this->registry);
        });

        $this->assertTrue($this->registry->has('LazyType'));
        $type = $this->registry->get('LazyType');
        $this->assertInstanceOf(U8::class, $type);
    }

    public function testCallableFactoryCached(): void
    {
        $callCount = 0;
        $this->registry->register('CountedType', function () use (&$callCount) {
            $callCount++;
            return new U8($this->registry);
        });

        // First access
        $this->registry->get('CountedType');
        $this->assertEquals(1, $callCount);

        // Second access should use cache
        $this->registry->get('CountedType');
        $this->assertEquals(1, $callCount);
    }
}
