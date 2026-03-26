# Best Practices Guide

This guide covers best practices for using php-scale-codec effectively.

## Table of Contents

1. [Type Handling](#type-handling)
2. [Memory Management](#memory-management)
3. [Error Handling](#error-handling)
4. [Performance](#performance)
5. [Security](#security)
6. [Testing](#testing)

---

## Type Handling

### Use TypeRegistry for Type Lookup

```php
// Good: Centralize type management
$registry = new TypeRegistry();
$u32 = $registry->get('U32');

// Avoid: Creating types directly
$u32 = new U32Type();
```

### Handle Large Integers Properly

```php
// U64/U128 may exceed PHP_INT_MAX
$u64 = $registry->get('U64');

// Good: Use string for large values
$encoded = $u64->encode('18446744073709551615');

// Good: Decode returns string for large values
$value = $u64->decode($bytes); // Returns string, not int

// Use GMP for arithmetic on large values
$sum = gmp_add($value, '1000');
```

### Type Validation

```php
// Validate before encoding
if ($u8->isValid($value)) {
    $encoded = $u8->encode($value);
} else {
    throw new InvalidArgumentException("Invalid U8 value: $value");
}
```

---

## Memory Management

### Reuse ScaleBytes for Large Data

```php
// Good: Process in chunks for large data
$bytes = ScaleBytes::fromHex($largeHexString);
while (!$bytes->isEmpty()) {
    $chunk = $bytes->readBytes(1024);
    // Process chunk
}

// Avoid: Loading entire data into memory
$allData = $bytes->readAllBytes();
```

### Reset ScaleBytes for Reuse

```php
$bytes = ScaleBytes::fromHex('0x010203');

// Read some bytes
$first = $bytes->readBytes(2); // [1, 2]

// Reset to beginning if needed
$bytes->reset();
$again = $bytes->readBytes(2); // [1, 2] again
```

---

## Error Handling

### Use Specific Exceptions

```php
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;
use Substrate\ScaleCodec\Exception\InvalidTypeException;

try {
    $encoded = $type->encode($data);
} catch (ScaleEncodeException $e) {
    // Handle encoding error
    error_log("Encoding failed: " . $e->getMessage());
} catch (InvalidTypeException $e) {
    // Handle type mismatch
    error_log("Invalid type: " . $e->getMessage());
}
```

### Validate Input Data

```php
function encodeAccountBalance($balance): ScaleBytes {
    if (!is_string($balance) && !is_int($balance)) {
        throw new InvalidArgumentException("Balance must be string or int");
    }
    
    if (gmp_cmp($balance, '0') < 0) {
        throw new InvalidArgumentException("Balance cannot be negative");
    }
    
    return $this->u128->encode($balance);
}
```

---

## Performance

### Cache Type Instances

```php
// Good: Cache frequently used types
class ScaleService {
    private TypeRegistry $registry;
    private ?ScaleTypeInterface $cachedU32 = null;
    
    public function encodeU32(int $value): ScaleBytes {
        if ($this->cachedU32 === null) {
            $this->cachedU32 = $this->registry->get('U32');
        }
        return $this->cachedU32->encode($value);
    }
}
```

### Batch Operations

```php
// Good: Batch multiple operations
$encoded = '';
foreach ($items as $item) {
    $encoded .= $type->encode($item)->toBytes();
}

// Or use Vec for collections
$vec = (new VecType($registry))->setElementType($type);
$encoded = $vec->encode($items);
```

### Use Compact for Large Integers

```php
// Compact encoding is more efficient for small-to-medium values
$compact = $registry->get('Compact');

// 0-63: 1 byte
// 64-16383: 2 bytes
// 16384-1073741823: 4 bytes
// >1073741823: variable

$encoded = $compact->encode(42); // Only 1 byte
```

---

## Security

### Never Hardcode Private Keys

```php
// Bad: Hardcoded private key
$keypair = Keypair::fromSeed('0x1234...');

// Good: Load from environment
$seed = getenv('SUBSTRATE_SEED');
$keypair = Keypair::fromSeed($seed);

// Better: Use hardware wallet or key management service
```

### Validate External Input

```php
function decodeUserInput(string $hexInput) {
    // Validate hex format
    if (!preg_match('/^0x[0-9a-fA-F]+$/', $hexInput)) {
        throw new InvalidArgumentException("Invalid hex format");
    }
    
    // Limit input size
    if (strlen($hexInput) > 100000) {
        throw new InvalidArgumentException("Input too large");
    }
    
    return $this->decode(ScaleBytes::fromHex($hexInput));
}
```

### Use Secure Random for Nonces

```php
// Good: Use secure random
$nonce = random_int(0, PHP_INT_MAX);

// Bad: Predictable random
$nonce = rand();
```

---

## Testing

### Unit Test All Custom Encodings

```php
class MyTypeTest extends TestCase {
    public function testEncodeDecode(): void {
        $type = new MyCustomType();
        
        $original = ['field1' => 123, 'field2' => 'test'];
        $encoded = $type->encode($original);
        $decoded = $type->decode(ScaleBytes::fromHex($encoded->toHex()));
        
        $this->assertEquals($original, $decoded);
    }
}
```

### Use Compatibility Tests

```php
// Compare against known-good polkadot.js output
public function testCompatibilityWithPolkadotJs(): void {
    $knownGood = '0x12345678'; // From polkadot.js
    
    $type = $this->registry->get('U32');
    $encoded = $type->encode(305419896);
    
    $this->assertEquals($knownGood, $encoded->toHex());
}
```

### Test Edge Cases

```php
public function testEdgeCases(): void {
    $u8 = $this->registry->get('U8');
    
    // Min value
    $this->assertEquals('0x00', $u8->encode(0)->toHex());
    
    // Max value
    $this->assertEquals('0xff', $u8->encode(255)->toHex());
    
    // Overflow should throw
    $this->expectException(ScaleEncodeException::class);
    $u8->encode(256);
}
```

---

## Common Patterns

### Builder Pattern for Complex Types

```php
class CallBuilder {
    private StructType $callType;
    private array $data = [];
    
    public function __construct(TypeRegistry $registry) {
        $this->callType = (new StructType($registry))
            ->setFields([
                'palletIndex' => $registry->get('U8'),
                'callIndex' => $registry->get('U8'),
                'args' => $registry->get('Bytes'),
            ]);
    }
    
    public function setPallet(int $index): self {
        $this->data['palletIndex'] = $index;
        return $this;
    }
    
    public function setCall(int $index): self {
        $this->data['callIndex'] = $index;
        return $this;
    }
    
    public function setArgs(array $args): self {
        $this->data['args'] = $args;
        return $this;
    }
    
    public function build(): ScaleBytes {
        return $this->callType->encode($this->data);
    }
}
```

### Factory Pattern for Type Creation

```php
class TypeFactory {
    private TypeRegistry $registry;
    
    public function createVec(string $elementType): VecType {
        return (new VecType($this->registry))
            ->setElementType($this->registry->get($elementType));
    }
    
    public function createOption(string $innerType): OptionType {
        return (new OptionType($this->registry))
            ->setInnerType($this->registry->get($innerType));
    }
}
```

---

## Debugging Tips

### Enable Verbose Logging

```php
// Log encoding operations
$encoded = $type->encode($value);
error_log("Encoded {$value} to " . $encoded->toHex());

// Log decoding operations
$bytes = ScaleBytes::fromHex($hex);
error_log("Decoding from position " . $bytes->getPosition());
$decoded = $type->decode($bytes);
error_log("Decoded to " . json_encode($decoded));
```

### Inspect Byte Representation

```php
$bytes = ScaleBytes::fromHex('0x0102030405');

// Get remaining bytes
while (!$bytes->isEmpty()) {
    echo sprintf("Position %d: 0x%02x\n", $bytes->getPosition(), $bytes->readByte());
}
```
