<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Types;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Types\{TypeRegistry, BoolType, NullType};
use Substrate\ScaleCodec\Exception\InvalidTypeException;

class TypeRegistryTest extends TestCase
{
    private TypeRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new TypeRegistry();
    }

    public function testRegisterAndGetType(): void
    {
        $boolType = new BoolType($this->registry);
        $this->registry->register('bool', $boolType);

        $retrieved = $this->registry->get('bool');
        $this->assertInstanceOf(BoolType::class, $retrieved);
    }

    public function testGetCaseInsensitive(): void
    {
        $boolType = new BoolType($this->registry);
        $this->registry->register('Bool', $boolType);

        $this->assertInstanceOf(BoolType::class, $this->registry->get('bool'));
        $this->assertInstanceOf(BoolType::class, $this->registry->get('BOOL'));
    }

    public function testHasType(): void
    {
        $this->assertFalse($this->registry->has('bool'));

        $boolType = new BoolType($this->registry);
        $this->registry->register('bool', $boolType);

        $this->assertTrue($this->registry->has('bool'));
    }

    public function testRegisterAlias(): void
    {
        $boolType = new BoolType($this->registry);
        $this->registry->register('bool', $boolType);
        $this->registry->registerAlias('boolean', 'bool');

        $this->assertInstanceOf(BoolType::class, $this->registry->get('boolean'));
    }

    public function testGetUnregisteredTypeThrowsException(): void
    {
        $this->expectException(InvalidTypeException::class);
        $this->registry->get('unregistered');
    }

    public function testFreezeRegistry(): void
    {
        $boolType = new BoolType($this->registry);
        $this->registry->register('bool', $boolType);
        $this->registry->freeze();

        $this->assertTrue($this->registry->isFrozen());

        $this->expectException(InvalidTypeException::class);
        $nullType = new NullType($this->registry);
        $this->registry->register('null', $nullType);
    }

    public function testGetRegisteredTypes(): void
    {
        $boolType = new BoolType($this->registry);
        $nullType = new NullType($this->registry);

        $this->registry->register('bool', $boolType);
        $this->registry->register('null', $nullType);

        $types = $this->registry->getRegisteredTypes();
        $this->assertCount(2, $types);
        $this->assertContains('bool', $types);
        $this->assertContains('null', $types);
    }

    public function testClone(): void
    {
        $boolType = new BoolType($this->registry);
        $this->registry->register('bool', $boolType);

        $clonedRegistry = $this->registry->clone();
        $this->assertTrue($clonedRegistry->has('bool'));
    }
}