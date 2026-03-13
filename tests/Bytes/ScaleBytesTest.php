<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Bytes;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Bytes\ScaleBytes;

class ScaleBytesTest extends TestCase
{
    public function testFromHexString(): void
    {
        $bytes = ScaleBytes::fromHex('0x01020304');
        $this->assertEquals([1, 2, 3, 4], $bytes->toBytes());
    }

    public function testFromHexStringWithoutPrefix(): void
    {
        $bytes = ScaleBytes::fromHex('01020304');
        $this->assertEquals([1, 2, 3, 4], $bytes->toBytes());
    }

    public function testFromByteArray(): void
    {
        $bytes = ScaleBytes::fromBytes([1, 2, 3, 4]);
        $this->assertEquals([1, 2, 3, 4], $bytes->toBytes());
    }

    public function testReadBytes(): void
    {
        $bytes = ScaleBytes::fromHex('01020304');
        $this->assertEquals([1, 2], $bytes->readBytes(2));
        $this->assertEquals([3, 4], $bytes->readBytes(2));
    }

    public function testPeekBytes(): void
    {
        $bytes = ScaleBytes::fromHex('01020304');
        $this->assertEquals([1, 2], $bytes->peekBytes(2));
        $this->assertEquals([1, 2], $bytes->peekBytes(2));
    }

    public function testReadByte(): void
    {
        $bytes = ScaleBytes::fromHex('0102');
        $this->assertEquals(1, $bytes->readByte());
        $this->assertEquals(2, $bytes->readByte());
    }

    public function testRemaining(): void
    {
        $bytes = ScaleBytes::fromHex('01020304');
        $this->assertEquals(4, $bytes->remaining());
        $bytes->readBytes(2);
        $this->assertEquals(2, $bytes->remaining());
    }

    public function testLength(): void
    {
        $bytes = ScaleBytes::fromHex('01020304');
        $this->assertEquals(4, $bytes->length());
    }

    public function testHasRemaining(): void
    {
        $bytes = ScaleBytes::fromHex('0102');
        $this->assertTrue($bytes->hasRemaining());
        $bytes->readBytes(2);
        $this->assertFalse($bytes->hasRemaining());
    }

    public function testIsExhausted(): void
    {
        $bytes = ScaleBytes::fromHex('0102');
        $this->assertFalse($bytes->isExhausted());
        $bytes->readBytes(2);
        $this->assertTrue($bytes->isExhausted());
    }

    public function testReset(): void
    {
        $bytes = ScaleBytes::fromHex('010203');
        $bytes->readBytes(2);
        $this->assertEquals(1, $bytes->remaining());
        $bytes->reset();
        $this->assertEquals(3, $bytes->remaining());
    }

    public function testToHex(): void
    {
        $bytes = ScaleBytes::fromBytes([1, 2, 3, 4]);
        $this->assertEquals('0x01020304', $bytes->toHex());
        $this->assertEquals('01020304', $bytes->toHex(false));
    }

    public function testConcat(): void
    {
        $bytes1 = ScaleBytes::fromHex('0102');
        $bytes2 = ScaleBytes::fromHex('0304');
        $result = $bytes1->concat($bytes2);
        $this->assertEquals([1, 2, 3, 4], $result->toBytes());
    }

    public function testSlice(): void
    {
        $bytes = ScaleBytes::fromHex('0102030405');
        $slice = $bytes->slice(1, 3);
        $this->assertEquals([2, 3, 4], $slice->toBytes());
    }

    public function testToString(): void
    {
        $bytes = ScaleBytes::fromBytes([1, 2, 3]);
        $this->assertEquals('0x010203', (string)$bytes);
    }

    public function testEmpty(): void
    {
        $bytes = ScaleBytes::empty();
        $this->assertEquals(0, $bytes->length());
        $this->assertEquals([], $bytes->toBytes());
    }

    public function testInsufficientBytesThrowsException(): void
    {
        $this->expectException(\Substrate\ScaleCodec\Exception\ScaleDecodeException::class);
        
        $bytes = ScaleBytes::fromHex('01');
        $bytes->readBytes(2);
    }
}