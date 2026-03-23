<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Types;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Types\{TypeRegistry, BytesType, StringType, AccountIdType, MultiAddressType, BoolType, NullType};

class SpecialTypeTest extends TestCase
{
    private TypeRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new TypeRegistry();
    }

    // ==================== Bytes Tests ====================

    public function testBytesEncodeHex(): void
    {
        $bytes = new BytesType($this->registry);
        $result = $bytes->encode('0x01020304');
        // Compact(4) = 0x10, then 0x01020304
        $this->assertEquals('0x1001020304', $result->toHex());
    }

    public function testBytesEncodeArray(): void
    {
        $bytes = new BytesType($this->registry);
        $result = $bytes->encode([1, 2, 3, 4]);
        $this->assertEquals('0x1001020304', $result->toHex());
    }

    public function testBytesEncodeRawString(): void
    {
        $bytes = new BytesType($this->registry);
        $result = $bytes->encode('test');
        // 'test' = [116, 101, 115, 116], Compact(4) = 0x10
        $this->assertEquals('0x1074657374', $result->toHex());
    }

    public function testBytesDecode(): void
    {
        $bytes = new BytesType($this->registry);
        $encoded = ScaleBytes::fromHex('0x1001020304');
        $result = $bytes->decode($encoded);
        $this->assertEquals('0x01020304', $result);
    }

    public function testBytesRoundTrip(): void
    {
        $bytes = new BytesType($this->registry);
        $original = '0x0102030405060708090a';
        $encoded = $bytes->encode($original);
        $decoded = $bytes->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(strtolower($original), strtolower($decoded));
    }

    // ==================== String Tests ====================

    public function testStringEncode(): void
    {
        $string = new StringType($this->registry);
        $result = $string->encode('hello');
        // 'hello' = 5 bytes, Compact(5) = 0x14
        // h=104, e=101, l=108, l=108, o=111
        $this->assertEquals('0x1468656c6c6f', $result->toHex());
    }

    public function testStringEncodeEmpty(): void
    {
        $string = new StringType($this->registry);
        $result = $string->encode('');
        $this->assertEquals('0x00', $result->toHex());
    }

    public function testStringDecode(): void
    {
        $string = new StringType($this->registry);
        $bytes = ScaleBytes::fromHex('0x1468656c6c6f');
        $result = $string->decode($bytes);
        $this->assertEquals('hello', $result);
    }

    public function testStringRoundTrip(): void
    {
        $string = new StringType($this->registry);
        $values = ['', 'a', 'hello', '你好世界', 'test123!@#'];
        
        foreach ($values as $value) {
            $encoded = $string->encode($value);
            $decoded = $string->decode(ScaleBytes::fromBytes($encoded->toBytes()));
            $this->assertEquals($value, $decoded);
        }
    }

    // ==================== AccountId Tests ====================

    public function testAccountIdEncodeHex(): void
    {
        $accountId = new AccountIdType($this->registry);
        // 32-byte hex
        $hex = '0x' . str_repeat('01', 32);
        $result = $accountId->encode($hex);
        $this->assertEquals(strtolower($hex), $result->toHex());
    }

    public function testAccountIdDecode(): void
    {
        $accountId = new AccountIdType($this->registry);
        $hex = '0x' . str_repeat('aa', 32);
        $encoded = ScaleBytes::fromHex(substr($hex, 2));
        $result = $accountId->decode($encoded);
        $this->assertEquals(strtolower($hex), strtolower($result));
    }

    public function testAccountIdRoundTrip(): void
    {
        $accountId = new AccountIdType($this->registry);
        $hex = '0x' . bin2hex(random_bytes(32));
        $encoded = $accountId->encode($hex);
        $decoded = $accountId->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(strtolower($hex), strtolower($decoded));
    }

    public function testAccountIdEthereum(): void
    {
        $accountId = new AccountIdType($this->registry);
        $accountId->setExpectedLength(20);
        
        // Ethereum address (20 bytes)
        $hex = '0x' . str_repeat('ff', 20);
        $encoded = $accountId->encode($hex);
        $decoded = $accountId->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        $this->assertEquals(strtolower($hex), strtolower($decoded));
    }

    // ==================== MultiAddress Tests ====================

    public function testMultiAddressEncodeId(): void
    {
        $multi = new MultiAddressType($this->registry);
        $address = '0x' . str_repeat('01', 32);
        $result = $multi->encode(['Id' => $address]);
        // Variant index 0 + 32 bytes
        $this->assertEquals('0x00' . substr($address, 2), $result->toHex());
    }

    public function testMultiAddressDecodeId(): void
    {
        $multi = new MultiAddressType($this->registry);
        $address = '0x' . str_repeat('aa', 32);
        $bytes = ScaleBytes::fromHex('0x00' . str_repeat('aa', 32));
        $result = $multi->decode($bytes);
        $this->assertEquals(['Id' => strtolower($address)], $result);
    }

    public function testMultiAddressEncodeIndex(): void
    {
        $multi = new MultiAddressType($this->registry);
        $result = $multi->encode(['Index' => 42]);
        // Variant 1 + Compact(42) = 0x01 + 0xa8 (42 << 2 = 168 = 0xa8)
        $this->assertEquals('0x01a8', $result->toHex());
    }

    public function testMultiAddressDecodeIndex(): void
    {
        $multi = new MultiAddressType($this->registry);
        $bytes = ScaleBytes::fromHex('0x01a8');
        $result = $multi->decode($bytes);
        $this->assertEquals(['Index' => 42], $result);
    }

    public function testMultiAddressEncodeAddress20(): void
    {
        $multi = new MultiAddressType($this->registry);
        $address = '0x' . str_repeat('ff', 20);
        $result = $multi->encode(['Address20' => $address]);
        // Variant 4 + 20 bytes
        $this->assertEquals('0x04' . str_repeat('ff', 20), substr($result->toHex(), 0, 44));
    }

    public function testMultiAddressDecodeAddress20(): void
    {
        $multi = new MultiAddressType($this->registry);
        $bytes = ScaleBytes::fromHex('0x04' . str_repeat('ff', 20));
        $result = $multi->decode($bytes);
        $this->assertEquals(['Address20' => '0x' . str_repeat('ff', 20)], $result);
    }

    public function testMultiAddressRoundTripId(): void
    {
        $multi = new MultiAddressType($this->registry);
        $address = '0x' . bin2hex(random_bytes(32));
        
        $encoded = $multi->encode(['Id' => $address]);
        $decoded = $multi->decode(ScaleBytes::fromBytes($encoded->toBytes()));
        
        $this->assertEquals(['Id' => strtolower($address)], $decoded);
    }

    // ==================== Bool Tests (already exists) ====================

    public function testBoolEncode(): void
    {
        $bool = new BoolType($this->registry);
        
        $this->assertEquals('0x00', $bool->encode(false)->toHex());
        $this->assertEquals('0x01', $bool->encode(true)->toHex());
    }

    public function testBoolDecode(): void
    {
        $bool = new BoolType($this->registry);
        
        $this->assertFalse($bool->decode(ScaleBytes::fromHex('0x00')));
        $this->assertTrue($bool->decode(ScaleBytes::fromHex('0x01')));
    }

    // ==================== Null Tests (already exists) ====================

    public function testNullEncode(): void
    {
        $null = new NullType($this->registry);
        $result = $null->encode(null);
        $this->assertEquals('0x', $result->toHex());
        $this->assertEquals(0, strlen($result->toHex()) - 2);
    }

    public function testNullDecode(): void
    {
        $null = new NullType($this->registry);
        // Null type doesn't read any bytes, so we can use empty bytes
        $result = $null->decode(ScaleBytes::empty());
        $this->assertNull($result);
    }
}