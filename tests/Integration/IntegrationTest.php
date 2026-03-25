<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, TypeFactory, U8, U32, U64, Compact, VecType, OptionType, StructType, EnumType};

class IntegrationTest extends TestCase
{
    private TypeRegistry $registry;
    private TypeFactory $factory;

    protected function setUp(): void
    {
        $this->registry = new TypeRegistry();
        $this->factory = new TypeFactory($this->registry);
    }

    // ==================== Nested Type Tests ====================

    public function testNestedVecVecU8(): void
    {
        $vec = new VecType($this->registry);
        $innerVec = new VecType($this->registry);
        $u8 = new U8($this->registry);
        
        $innerVec->setElementType($u8);
        $vec->setElementType($innerVec);

        $data = [[1, 2], [3, 4], [5, 6]];
        $encoded = $vec->encode($data);
        $decoded = $vec->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        
        $this->assertEquals($data, $decoded);
    }

    public function testOptionVecU32(): void
    {
        $option = new OptionType($this->registry);
        $vec = new VecType($this->registry);
        $u32 = new U32($this->registry);
        
        $vec->setElementType($u32);
        $option->setInnerType($vec);

        // Test Some
        $data = [100, 200, 300];
        $encoded = $option->encode($data);
        $decoded = $option->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals($data, $decoded);

        // Test None
        $encoded = $option->encode(null);
        $decoded = $option->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertNull($decoded);
    }

    public function testVecOptionU8(): void
    {
        $vec = new VecType($this->registry);
        $option = new OptionType($this->registry);
        $u8 = new U8($this->registry);
        
        $option->setInnerType($u8);
        $vec->setElementType($option);

        $data = [1, null, 3, null, 5];
        $encoded = $vec->encode($data);
        $decoded = $vec->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        
        $this->assertEquals($data, $decoded);
    }

    // ==================== Complex Struct Tests ====================

    public function testNestedStruct(): void
    {
        $outer = new StructType($this->registry);
        $inner = new StructType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);

        $inner->setFields([
            'x' => $u8,
            'y' => $u32,
        ]);

        $outer->setFields([
            'id' => $u8,
            'inner' => $inner,
        ]);

        $data = [
            'id' => 1,
            'inner' => ['x' => 10, 'y' => 1000],
        ];

        $encoded = $outer->encode($data);
        $decoded = $outer->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        
        $this->assertEquals($data, $decoded);
    }

    // ==================== Complex Enum Tests ====================

    public function testEnumWithStructVariant(): void
    {
        $enum = new EnumType($this->registry);
        $struct = new StructType($this->registry);
        $u8 = new U8($this->registry);
        $u32 = new U32($this->registry);

        $struct->setFields([
            'code' => $u8,
            'message' => $u32,
        ]);

        $enum->addVariant('None', 0);
        $enum->addVariant('Error', 1, $struct);

        // Test unit variant
        $encoded = $enum->encode(['None' => null]);
        $decoded = $enum->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(['None' => null], $decoded);

        // Test struct variant
        $data = ['Error' => ['code' => 404, 'message' => 12345]];
        $encoded = $enum->encode($data);
        $decoded = $enum->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals($data, $decoded);
    }

    // ==================== TypeFactory Integration Tests ====================

    public function testFactoryCreateVecU8(): void
    {
        $type = $this->factory->create('Vec<U8>');
        $this->assertInstanceOf(VecType::class, $type);
    }

    public function testFactoryCreateOptionU32(): void
    {
        $type = $this->factory->create('Option<U32>');
        $this->assertInstanceOf(OptionType::class, $type);
    }

    public function testFactoryCreateNestedTypes(): void
    {
        $type = $this->factory->create('Vec<Option<U8>>');
        $this->assertInstanceOf(VecType::class, $type);
    }

    // ==================== Real-world Scenario Tests ====================

    public function testBalanceTransferScenario(): void
    {
        // Simulate a balance transfer call
        $call = new StructType($this->registry);
        $compact = new Compact($this->registry);
        $u64 = new U64($this->registry);

        // In a real scenario, we'd use proper address types
        // This is a simplified test
        $call->setFields([
            'value' => $compact,
        ]);

        $data = ['value' => 1000000000];
        $encoded = $call->encode($data);
        $decoded = $call->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        
        $this->assertEquals($data, $decoded);
    }

    public function testBatchTransactions(): void
    {
        $vec = new VecType($this->registry);
        $u8 = new U8($this->registry);
        
        $vec->setElementType($u8);

        // Simulate batch of calls
        $data = [1, 2, 3, 4, 5];
        $encoded = $vec->encode($data);
        $decoded = $vec->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        
        $this->assertEquals($data, $decoded);
    }
}
