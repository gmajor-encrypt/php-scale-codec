<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Bytes;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Bytes\ScaleBytes;
use InvalidArgumentException;

class ScaleBytesComprehensiveTest extends TestCase
{
    // ==================== Creation Tests ====================

    public function testCreateFromHexWithPrefix(): void
    {
        $bytes = ScaleBytes::fromHex('0x010203');
        $this->assertEquals([1, 2, 3], $bytes->toBytes());
    }

    public function testCreateFromHexWithoutPrefix(): void
    {
        $bytes = ScaleBytes::fromHex('010203');
        $this->assertEquals([1, 2, 3], $bytes->toBytes());
    }

    public function testCreateFromBytes(): void
    {
        $bytes = ScaleBytes::fromBytes([1, 2, 3]);
        $this->assertEquals([1, 2, 3], $bytes->toBytes());
    }

    public function testCreateEmpty(): void
    {
        $bytes = ScaleBytes::empty();
        $this->assertEquals([], $bytes->toBytes());
        $this->assertEquals(0, $bytes->length());
    }

    public function testInvalidHexThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ScaleBytes::fromHex('invalid');
    }

    // ==================== Read Tests ====================

    public function testReadByte(): void
    {
        $bytes = ScaleBytes::fromHex('0x010203');
        $this->assertEquals(1, $bytes->readByte());
        $this->assertEquals(2, $bytes->readByte());
        $this->assertEquals(3, $bytes->readByte());
    }

    public function testReadBytes(): void
    {
        $bytes = ScaleBytes::fromHex('0x0102030405');
        $this->assertEquals([1, 2], $bytes->readBytes(2));
        $this->assertEquals([3, 4, 5], $bytes->readBytes(3));
    }

    public function testPeekByte(): void
    {
        $bytes = ScaleBytes::fromHex('0x010203');
        $this->assertEquals(1, $bytes->peekByte());
        $this->assertEquals(1, $bytes->readByte()); // Offset not moved by peek
    }

    public function testPeekBytes(): void
    {
        $bytes = ScaleBytes::fromHex('0x0102030405');
        $this->assertEquals([1, 2, 3], $bytes->peekBytes(3));
        $this->assertEquals(1, $bytes->readByte()); // Offset not moved by peek
    }

    public function testReadBeyondEndThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $bytes = ScaleBytes::fromHex('0x0102');
        $bytes->readBytes(3);
    }

    // ==================== State Tests ====================

    public function testRemaining(): void
    {
        $bytes = ScaleBytes::fromHex('0x010203');
        $this->assertEquals(3, $bytes->remaining());
        $bytes->readByte();
        $this->assertEquals(2, $bytes->remaining());
    }

    public function testLength(): void
    {
        $bytes = ScaleBytes::fromHex('0x010203');
        $this->assertEquals(3, $bytes->length());
    }

    public function testHasRemaining(): void
    {
        $bytes = ScaleBytes::fromHex('0x0102');
        $this->assertTrue($bytes->hasRemaining());
        $bytes->readBytes(2);
        $this->assertFalse($bytes->hasRemaining());
    }

    public function testIsExhausted(): void
    {
        $bytes = ScaleBytes::fromHex('0x0102');
        $this->assertFalse($bytes->isExhausted());
        $bytes->readBytes(2);
        $this->assertTrue($bytes->isExhausted());
    }

    public function testGetOffset(): void
    {
        $bytes = ScaleBytes::fromHex('0x010203');
        $this->assertEquals(0, $bytes->getOffset());
        $bytes->readByte();
        $this->assertEquals(1, $bytes->getOffset());
    }

    // ==================== Manipulation Tests ====================

    public function testConcat(): void
    {
        $bytes1 = ScaleBytes::fromHex('0x0102');
        $bytes2 = ScaleBytes::fromHex('0x0304');
        $result = $bytes1->concat($bytes2);
        $this->assertEquals([1, 2, 3, 4], $result->toBytes());
    }

    public function testConcatEmpty(): void
    {
        $bytes = ScaleBytes::fromHex('0x0102');
        $empty = ScaleBytes::empty();
        $result = $bytes->concat($empty);
        $this->assertEquals([1, 2], $result->toBytes());
    }

    public function testSlice(): void
    {
        $bytes = ScaleBytes::fromHex('0x0102030405');
        $slice = $bytes->slice(1, 3);
        $this->assertEquals([2, 3, 4], $slice->toBytes());
    }

    public function testSliceToEnd(): void
    {
        $bytes = ScaleBytes::fromHex('0x0102030405');
        $slice = $bytes->slice(2);
        $this->assertEquals([3, 4, 5], $slice->toBytes());
    }

    public function testReset(): void
    {
        $bytes = ScaleBytes::fromHex('0x010203');
        $bytes->readBytes(2);
        $this->assertEquals(2, $bytes->getOffset());
        $bytes->reset();
        $this->assertEquals(0, $bytes->getOffset());
    }

    // ==================== Output Tests ====================

    public function testToHex(): void
    {
        $bytes = ScaleBytes::fromBytes([1, 2, 3]);
        $this->assertEquals('0x010203', $bytes->toHex());
        $this->assertEquals('010203', $bytes->toHex(false));
    }

    public function testToString(): void
    {
        $bytes = ScaleBytes::fromBytes([1, 2, 3]);
        $this->assertEquals('0x010203', (string) $bytes);
    }

    // ==================== Edge Case Tests ====================

    public function testEmptyBytes(): void
    {
        $bytes = ScaleBytes::empty();
        $this->assertEquals(0, $bytes->length());
        $this->assertEquals(0, $bytes->remaining());
        $this->assertEquals('0x', $bytes->toHex());
    }

    public function testLargeByteArray(): void
    {
        $data = range(0, 255);
        $bytes = ScaleBytes::fromBytes($data);
        $this->assertEquals(256, $bytes->length());
        $this->assertEquals($data, $bytes->toBytes());
    }

    public function testAllZeroBytes(): void
    {
        $bytes = ScaleBytes::fromHex('0x000000');
        $this->assertEquals([0, 0, 0], $bytes->toBytes());
    }

    public function testAllMaxBytes(): void
    {
        $bytes = ScaleBytes::fromHex('0xffffff');
        $this->assertEquals([255, 255, 255], $bytes->toBytes());
    }
}
