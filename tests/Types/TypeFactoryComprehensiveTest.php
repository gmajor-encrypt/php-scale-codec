<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Types;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Types\{TypeRegistry, TypeFactory, VecType, OptionType, TupleType, FixedArrayType, U8, U32};
use Substrate\ScaleCodec\Exception\InvalidTypeException;

class TypeFactoryComprehensiveTest extends TestCase
{
    private TypeRegistry $registry;
    private TypeFactory $factory;

    protected function setUp(): void
    {
        $this->registry = new TypeRegistry();
        $this->factory = new TypeFactory($this->registry);
    }

    // ==================== Simple Type Creation Tests ====================

    public function testCreateU8(): void
    {
        $type = $this->factory->create('U8');
        $this->assertInstanceOf(U8::class, $type);
    }

    public function testCreateU32(): void
    {
        $type = $this->factory->create('U32');
        $this->assertInstanceOf(U32::class, $type);
    }

    public function testCreateCaseInsensitive(): void
    {
        $type1 = $this->factory->create('u8');
        $type2 = $this->factory->create('U8');
        $type3 = $this->factory->create('U8');
        
        $this->assertInstanceOf(U8::class, $type1);
        $this->assertInstanceOf(U8::class, $type2);
        $this->assertInstanceOf(U8::class, $type3);
    }

    // ==================== Parameterized Type Tests ====================

    public function testCreateVecU8(): void
    {
        $type = $this->factory->create('Vec<U8>');
        $this->assertInstanceOf(VecType::class, $type);
    }

    public function testCreateVecU32(): void
    {
        $type = $this->factory->create('Vec<U32>');
        $this->assertInstanceOf(VecType::class, $type);
    }

    public function testCreateOptionU8(): void
    {
        $type = $this->factory->create('Option<U8>');
        $this->assertInstanceOf(OptionType::class, $type);
    }

    public function testCreateNestedVecOption(): void
    {
        $type = $this->factory->create('Vec<Option<U8>>');
        $this->assertInstanceOf(VecType::class, $type);
    }

    public function testCreateOptionVec(): void
    {
        $type = $this->factory->create('Option<Vec<U8>>');
        $this->assertInstanceOf(OptionType::class, $type);
    }

    // ==================== Tuple Type Tests ====================

    public function testCreateEmptyTuple(): void
    {
        $type = $this->factory->create('()');
        // Empty tuple should be Null
        $this->assertNotNull($type);
    }

    public function testCreateSingleElementTuple(): void
    {
        $type = $this->factory->create('(U8)');
        $this->assertInstanceOf(TupleType::class, $type);
    }

    public function testCreateMultipleElementTuple(): void
    {
        $type = $this->factory->create('(U8, U32, U64)');
        $this->assertInstanceOf(TupleType::class, $type);
    }

    public function testCreateNestedTuple(): void
    {
        $type = $this->factory->create('(U8, (U32, U64))');
        $this->assertInstanceOf(TupleType::class, $type);
    }

    // ==================== Fixed Array Tests ====================

    public function testCreateFixedArrayU8(): void
    {
        $type = $this->factory->create('[U8; 32]');
        $this->assertInstanceOf(FixedArrayType::class, $type);
    }

    public function testCreateFixedArrayU32(): void
    {
        $type = $this->factory->create('[U32; 16]');
        $this->assertInstanceOf(FixedArrayType::class, $type);
    }

    public function testCreateFixedArrayWithSpaces(): void
    {
        $type = $this->factory->create('[U8;32]');
        $this->assertInstanceOf(FixedArrayType::class, $type);
    }

    // ==================== Invalid Type Tests ====================

    public function testInvalidTypeThrowsException(): void
    {
        $this->expectException(InvalidTypeException::class);
        $this->factory->create('NonExistentType');
    }

    public function testInvalidVecParameterCount(): void
    {
        // Vec requires exactly one type parameter
        $this->expectException(InvalidTypeException::class);
        $this->factory->create('Vec');
    }

    public function testInvalidFixedArrayFormat(): void
    {
        $this->expectException(InvalidTypeException::class);
        $this->factory->create('[U8]');
    }

    public function testZeroLengthFixedArrayThrows(): void
    {
        $this->expectException(InvalidTypeException::class);
        $this->factory->create('[U8; 0]');
    }

    // ==================== Validation Tests ====================

    public function testIsValidTypeStringForValidTypes(): void
    {
        $this->assertTrue($this->factory->isValidTypeString('U8'));
        $this->assertTrue($this->factory->isValidTypeString('Vec<U8>'));
        $this->assertTrue($this->factory->isValidTypeString('Option<U32>'));
        $this->assertTrue($this->factory->isValidTypeString('(U8, U32)'));
        $this->assertTrue($this->factory->isValidTypeString('[U8; 32]'));
    }

    public function testIsValidTypeStringForInvalidTypes(): void
    {
        $this->assertFalse($this->factory->isValidTypeString('InvalidType'));
        $this->assertFalse($this->factory->isValidTypeString('Vec'));
    }

    // ==================== Cache Tests ====================

    public function testTypeCaching(): void
    {
        $type1 = $this->factory->create('Vec<U8>');
        $type2 = $this->factory->create('Vec<U8>');
        
        // Should be different instances (clones from cache)
        $this->assertNotSame($type1, $type2);
    }

    public function testClearCache(): void
    {
        $this->factory->create('Vec<U8>');
        $this->factory->clearCache();
        // Should still work after clear
        $type = $this->factory->create('Vec<U8>');
        $this->assertInstanceOf(VecType::class, $type);
    }

    // ==================== Edge Cases ====================

    public function testWhitespaceHandling(): void
    {
        $type = $this->factory->create('  Vec< U8 >  ');
        $this->assertInstanceOf(VecType::class, $type);
    }

    public function testComplexNestedType(): void
    {
        $type = $this->factory->create('Option<Vec<(U8, U32)>>');
        $this->assertInstanceOf(OptionType::class, $type);
    }
}
