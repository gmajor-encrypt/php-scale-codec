<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Types;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Types\ScaleType;

class ScaleTypeTest extends TestCase
{
    public function testIsUnsignedInt(): void
    {
        $this->assertTrue(ScaleType::U8->isUnsignedInt());
        $this->assertTrue(ScaleType::U16->isUnsignedInt());
        $this->assertTrue(ScaleType::U32->isUnsignedInt());
        $this->assertTrue(ScaleType::U64->isUnsignedInt());
        $this->assertTrue(ScaleType::U128->isUnsignedInt());
        $this->assertFalse(ScaleType::I8->isUnsignedInt());
        $this->assertFalse(ScaleType::Bool->isUnsignedInt());
    }

    public function testIsSignedInt(): void
    {
        $this->assertTrue(ScaleType::I8->isSignedInt());
        $this->assertTrue(ScaleType::I16->isSignedInt());
        $this->assertTrue(ScaleType::I32->isSignedInt());
        $this->assertTrue(ScaleType::I64->isSignedInt());
        $this->assertTrue(ScaleType::I128->isSignedInt());
        $this->assertFalse(ScaleType::U8->isSignedInt());
        $this->assertFalse(ScaleType::Bool->isSignedInt());
    }

    public function testIsInteger(): void
    {
        $this->assertTrue(ScaleType::U8->isInteger());
        $this->assertTrue(ScaleType::I32->isInteger());
        $this->assertFalse(ScaleType::Bool->isInteger());
        $this->assertFalse(ScaleType::String->isInteger());
    }

    public function testGetByteSize(): void
    {
        $this->assertEquals(1, ScaleType::U8->getByteSize());
        $this->assertEquals(2, ScaleType::U16->getByteSize());
        $this->assertEquals(4, ScaleType::U32->getByteSize());
        $this->assertEquals(8, ScaleType::U64->getByteSize());
        $this->assertEquals(16, ScaleType::U128->getByteSize());
        $this->assertNull(ScaleType::Bool->getByteSize());
    }

    public function testGetBitSize(): void
    {
        $this->assertEquals(8, ScaleType::U8->getBitSize());
        $this->assertEquals(16, ScaleType::U16->getBitSize());
        $this->assertEquals(32, ScaleType::U32->getBitSize());
        $this->assertEquals(64, ScaleType::U64->getBitSize());
        $this->assertNull(ScaleType::Bool->getBitSize());
    }

    public function testFromTypeString(): void
    {
        $this->assertEquals(ScaleType::U8, ScaleType::fromTypeString('u8'));
        $this->assertEquals(ScaleType::U8, ScaleType::fromTypeString('U8'));
        $this->assertEquals(ScaleType::I32, ScaleType::fromTypeString('i32'));
        $this->assertEquals(ScaleType::Bool, ScaleType::fromTypeString('bool'));
    }

    public function testFromTypeStringWithAlias(): void
    {
        $this->assertEquals(ScaleType::I32, ScaleType::fromTypeString('int'));
        $this->assertEquals(ScaleType::U32, ScaleType::fromTypeString('uint'));
        $this->assertEquals(ScaleType::U128, ScaleType::fromTypeString('balance'));
    }

    public function testTryFromInvalidReturnsNull(): void
    {
        $this->assertNull(ScaleType::tryFrom('invalid_type'));
    }
}