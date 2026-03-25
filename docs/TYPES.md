# Types Reference

This document provides detailed reference for all SCALE types.

## Table of Contents

1. [Primitive Types](#primitive-types)
2. [Compact Types](#compact-types)
3. [Compound Types](#compound-types)
4. [Special Types](#special-types)
5. [Metadata Types](#metadata-types)

---

## Primitive Types

### Unsigned Integers

#### U8

8-bit unsigned integer (0-255).

```php
$type = $registry->get('U8');

// Encoding
$encoded = $type->encode(255);  // 0xff
$encoded = $type->encode(0);    // 0x00

// Decoding
$value = $type->decode(ScaleBytes::fromHex('0xff')); // 255
```

#### U16

16-bit unsigned integer (0-65535). Little-endian.

```php
$type = $registry->get('U16');
$encoded = $type->encode(256); // 0x0001
```

#### U32

32-bit unsigned integer. Little-endian.

```php
$type = $registry->get('U32');
$encoded = $type->encode(1000000); // 0x40420f00
```

#### U64

64-bit unsigned integer. Returns string for values > PHP_INT_MAX.

```php
$type = $registry->get('U64');
$encoded = $type->encode('18446744073709551615'); // Max value
$decoded = $type->decode($bytes); // Returns string
```

#### U128

128-bit unsigned integer. Always returns string.

```php
$type = $registry->get('U128');
$encoded = $type->encode('340282366920938463463374607431768211455');
```

### Signed Integers

#### I8

8-bit signed integer (-128 to 127).

```php
$type = $registry->get('I8');
$encoded = $type->encode(-1);  // 0xff
$encoded = $type->encode(-128); // 0x80
```

#### I16, I32, I64, I128

Signed versions follow two's complement encoding.

---

## Compact Types

### Compact

Variable-length integer encoding for efficient storage.

| Range | Prefix | Bytes |
|-------|--------|-------|
| 0-63 | 0b00 | 1 |
| 64-16383 | 0b01 | 2 |
| 16384-1073741823 | 0b10 | 4 |
| >1073741823 | 0b11 | variable |

```php
$compact = $registry->get('Compact');

// Single byte mode (0-63)
$compact->encode(0);   // 0x00
$compact->encode(63);  // 0xfc

// Two byte mode (64-16383)
$compact->encode(64);     // 0x0101
$compact->encode(16383);  // 0xfdff

// Four byte mode
$compact->encode(16384);  // 0x02000000
```

---

## Compound Types

### Vec\<T\>

Dynamically sized sequence of elements.

```php
$vecU8 = (new VecType($registry))->setElementType($registry->get('U8'));

// Empty vector
$vecU8->encode([]);  // 0x00

// Non-empty vector
$vecU8->encode([1, 2, 3]);  // 0x0c010203
```

### Option\<T\>

Optional value: None (0x00) or Some (0x01 + value).

```php
$optionU8 = (new OptionType($registry))->setInnerType($registry->get('U8'));

$optionU8->encode(null);  // 0x00
$optionU8->encode(42);    // 0x012a
```

### Tuple

Fixed-size sequence of heterogeneous types.

```php
$tuple = (new TupleType($registry))
    ->addElementType($registry->get('U8'))
    ->addElementType($registry->get('U32'));

$tuple->encode([255, 1000000]); // 0xff40420f00
```

### Struct

Named fields with specific types.

```php
$struct = (new StructType($registry))->setFields([
    'id' => $registry->get('U32'),
    'name' => $registry->get('String'),
]);

$struct->encode([
    'id' => 1,
    'name' => 'test',
]);
```

### Enum

Tagged union with variants.

```php
$enum = new EnumType($registry);
$enum->addVariant('VariantA', 0);
$enum->addVariant('VariantB', 1, $registry->get('U32'));

// Unit variant
$enum->encode(['VariantA' => null]); // 0x00

// Variant with data
$enum->encode(['VariantB' => 42]);   // 0x012a000000
```

### Result\<Ok, Err\>

Result type with success or error.

```php
$result = (new ResultType($registry))
    ->setOkType($registry->get('U32'))
    ->setErrType($registry->get('String'));

// Ok
$result->encode(['Ok' => 42]); // 0x002a000000

// Err
$result->encode(['Err' => 'error']); // 0x01106572726f72
```

---

## Special Types

### Bool

Boolean value.

```php
$bool = $registry->get('Bool');
$bool->encode(false); // 0x00
$bool->encode(true);  // 0x01
```

### String / Text

UTF-8 encoded string with length prefix.

```php
$string = $registry->get('String');
$string->encode('Hello'); // 0x1448656c6c6f
```

### Bytes

Raw byte sequence.

```php
$bytes = $registry->get('Bytes');
$bytes->encode([1, 2, 3, 4]);
```

### AccountId

32-byte account identifier.

```php
$accountId = $registry->get('AccountId');
$accountId->encode('5GrwvaEF5zXb26Fz9rcQpDWS57CtERHpNehXCPcNoHGKutQY');
```

### MultiAddress

Multi-address type for various address formats.

```php
$address = $registry->get('MultiAddress');
```

---

## Metadata Types

### Metadata

Parsed Substrate metadata.

```php
$parser = new MetadataParser();
$metadata = $parser->parse($metadataHex);
```

### Pallet

Individual pallet/module information.

```php
$pallet = $metadata->getPallet('System');
```

### TypeDefinition

Runtime type definition from metadata.

---

## Type Validation

All types support validation:

```php
$type->isValid($value); // Returns true/false

// Example
$u8->isValid(255);   // true
$u8->isValid(256);   // false
$u8->isValid(-1);    // false
```

---

## Error Handling

```php
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

// Encoding errors
try {
    $u8->encode(256); // Out of range
} catch (ScaleEncodeException $e) {
    // Handle error
}

// Decoding errors
try {
    $u32->decode($insufficientBytes);
} catch (ScaleDecodeException $e) {
    // Handle error
}
```
