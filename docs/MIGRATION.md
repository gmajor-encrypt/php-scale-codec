# Migration Guide: Upgrading to php-scale-codec v2.0

This guide helps you migrate from php-scale-codec v1.x to v2.0.

## Table of Contents

1. [Overview](#overview)
2. [Breaking Changes](#breaking-changes)
3. [API Changes](#api-changes)
4. [Type System Changes](#type-system-changes)
5. [Metadata Changes](#metadata-changes)
6. [Step-by-Step Migration](#step-by-step-migration)
7. [FAQ](#faq)

---

## Overview

Version 2.0 is a complete rewrite with:

- **PHP 8.2+ requirement** (from PHP 7.4+)
- **New type system** with better type safety
- **Improved performance** with optimized encoding/decoding
- **Metadata v12-v15 support** (previously v11-v14)
- **Modern API design** following PSR standards

---

## Breaking Changes

### PHP Version Requirement

```php
// v1.x: PHP 7.4+
// v2.0: PHP 8.2+

// Ensure your environment meets requirements
php -v // Should show 8.2.0 or higher
```

### Namespace Changes

```php
// v1.x
use Substrate\Scale\Codec;
use Substrate\Scale\Types\U32;

// v2.0
use Substrate\ScaleCodec\Types\TypeRegistry;
use Substrate\ScaleCodec\Bytes\ScaleBytes;
```

### Constructor Changes

```php
// v1.x
$codec = new Codec();
$u32 = new U32();

// v2.0
$registry = new TypeRegistry();
$u32 = $registry->get('U32');
```

---

## API Changes

### Encoding

```php
// v1.x
$codec = new Codec();
$encoded = $codec->encode(42, 'U32');
// Returns: string (hex)

// v2.0
$registry = new TypeRegistry();
$u32 = $registry->get('U32');
$encoded = $u32->encode(42);
// Returns: ScaleBytes object
echo $encoded->toHex(); // Get hex string
```

### Decoding

```php
// v1.x
$codec = new Codec();
$value = $codec->decode('0x2a000000', 'U32');
// Returns: mixed

// v2.0
$registry = new TypeRegistry();
$u32 = $registry->get('U32');
$bytes = ScaleBytes::fromHex('0x2a000000');
$value = $u32->decode($bytes);
// Returns: int|string
```

### Compact Integers

```php
// v1.x
$codec = new Codec();
$encoded = $codec->encodeCompact(1000);

// v2.0
$registry = new TypeRegistry();
$compact = $registry->get('Compact');
$encoded = $compact->encode(1000);
```

### Vectors

```php
// v1.x
$codec = new Codec();
$encoded = $codec->encode([1, 2, 3], 'Vec<U8>');

// v2.0
$registry = new TypeRegistry();
$vecU8 = (new VecType($registry))->setElementType($registry->get('U8'));
$encoded = $vecU8->encode([1, 2, 3]);
```

### Options

```php
// v1.x
$codec = new Codec();
$encoded = $codec->encode(null, 'Option<U32>');
$encoded = $codec->encode(42, 'Option<U32>');

// v2.0
$registry = new TypeRegistry();
$optionU32 = (new OptionType($registry))->setInnerType($registry->get('U32'));
$encoded = $optionU32->encode(null);
$encoded = $optionU32->encode(42);
```

---

## Type System Changes

### Type Registry (New)

v2.0 introduces a centralized type registry:

```php
// v2.0
$registry = new TypeRegistry();

// Get primitive types
$u8 = $registry->get('U8');
$u32 = $registry->get('U32');
$bool = $registry->get('Bool');
$string = $registry->get('String');
$compact = $registry->get('Compact');

// Register custom types
$registry->register('AccountId', $registry->get('Bytes32'));
```

### Large Integer Handling

```php
// v1.x: Large integers could overflow
$u64 = new U64();
$encoded = $u64->encode(18446744073709551615); // May lose precision

// v2.0: Use string for large integers
$u64 = $registry->get('U64');
$encoded = $u64->encode('18446744073709551615'); // String input
$decoded = $u64->decode($bytes); // Returns string for large values
```

### Type Factory (New)

```php
use Substrate\ScaleCodec\Types\TypeFactory;

$factory = new TypeFactory($registry);

// Create parameterized types
$vecU8 = $factory->create('Vec<U8>');
$optionU32 = $factory->create('Option<U32>');
$tuple = $factory->create('(U8, U32, Bool)');
```

---

## Metadata Changes

### Metadata Parsing

```php
// v1.x
$metadata = new Metadata($rawMetadata);
$pallet = $metadata->getModule('System');

// v2.0
$parser = new MetadataParser();
$metadata = $parser->parse($rawMetadataHex);
$pallet = $metadata->getPallet('System');
```

### Accessing Types

```php
// v1.x
$types = $metadata->getTypes();
$typeDef = $types->get($typeId);

// v2.0
$types = $metadata->getTypeRegistry();
$typeDef = $metadata->getType($typeId);
```

### Events

```php
// v1.x
$events = $metadata->getModuleEvents('System');

// v2.0
$events = $metadata->getPalletEvents('System');
```

---

## Step-by-Step Migration

### Step 1: Update Dependencies

```bash
# Update composer.json
composer require gmajor/substrate-codec-php:^2.0

# Or update manually
{
    "require": {
        "gmajor/substrate-codec-php": "^2.0"
    }
}

# Install
composer update
```

### Step 2: Update PHP Version

Ensure your environment runs PHP 8.2+:

```bash
php -v
# PHP 8.2.x (cli) ...
```

### Step 3: Update Imports

Replace old namespace imports:

```php
// Old (v1.x)
use Substrate\Scale\Codec;
use Substrate\Scale\Types\{U32, Vec, Option};

// New (v2.0)
use Substrate\ScaleCodec\Types\TypeRegistry;
use Substrate\ScaleCodec\Types\{VecType, OptionType};
use Substrate\ScaleCodec\Bytes\ScaleBytes;
```

### Step 4: Refactor Type Creation

Use TypeRegistry instead of direct instantiation:

```php
// Old
$u32 = new U32();
$vec = new Vec();

// New
$registry = new TypeRegistry();
$u32 = $registry->get('U32');
$vec = (new VecType($registry))->setElementType($registry->get('U8'));
```

### Step 5: Update Encoding/Decoding

```php
// Old
$codec = new Codec();
$hex = $codec->encode($value, 'U32');
$value = $codec->decode($hex, 'U32');

// New
$registry = new TypeRegistry();
$u32 = $registry->get('U32');
$bytes = $u32->encode($value);
$hex = $bytes->toHex();
$value = $u32->decode(ScaleBytes::fromHex($hex));
```

### Step 6: Update Error Handling

```php
// v2.0 has more specific exceptions
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;
use Substrate\ScaleCodec\Exception\InvalidTypeException;

try {
    $encoded = $type->encode($value);
} catch (ScaleEncodeException $e) {
    // Handle encoding errors
} catch (InvalidTypeException $e) {
    // Handle type mismatch
}
```

---

## Deprecated Methods

| v1.x Method | v2.0 Alternative |
|-------------|------------------|
| `Codec::encode()` | `TypeInterface::encode()` |
| `Codec::decode()` | `TypeInterface::decode()` |
| `Codec::encodeCompact()` | `Compact::encode()` |
| `Types\U32::fromHex()` | `ScaleBytes::fromHex()` |
| `Metadata::getModule()` | `Metadata::getPallet()` |
| `Metadata::getModuleEvents()` | `Metadata::getPalletEvents()` |

---

## FAQ

### Q: Can I use both v1.x and v2.0 in the same project?

**A:** Not recommended. The namespaces are different but having both versions may cause confusion. Complete the migration before deploying.

### Q: What about my existing encoded data?

**A:** The SCALE encoding format is unchanged. Data encoded with v1.x can be decoded with v2.0 and vice versa.

### Q: How do I handle large integers?

**A:** In v2.0, always use string for U64/U128 values:

```php
// Correct
$u64->encode('18446744073709551615');
$u64->encode('0'); // Even 0 as string for consistency

// The decoded value will be string for large values
$value = $u64->decode($bytes);
if (is_string($value)) {
    // Handle large integer
}
```

### Q: Where is the Codec class?

**A:** v2.0 doesn't have a central Codec class. Use TypeRegistry to get types:

```php
// Instead of Codec, use TypeRegistry
$registry = new TypeRegistry();
$type = $registry->get('U32');
```

### Q: How do I create a Vec with specific element type?

**A:** Use VecType with setElementType():

```php
$vecU8 = (new VecType($registry))->setElementType($registry->get('U8'));
$vecU32 = (new VecType($registry))->setElementType($registry->get('U32'));
```

### Q: How do I handle optional values?

**A:** Use OptionType:

```php
$option = (new OptionType($registry))->setInnerType($registry->get('U32'));
$encoded = $option->encode(null); // None
$encoded = $option->encode(42);   // Some(42)
```

### Q: What if I need a compatibility layer?

**A:** Create a wrapper class:

```php
class LegacyCodec {
    private TypeRegistry $registry;
    
    public function __construct() {
        $this->registry = new TypeRegistry();
    }
    
    public function encode($value, string $type): string {
        return $this->registry->get($type)->encode($value)->toHex();
    }
    
    public function decode(string $hex, string $type): mixed {
        return $this->registry->get($type)->decode(ScaleBytes::fromHex($hex));
    }
}
```

---

## Getting Help

- **Documentation**: [docs/API.md](API.md), [docs/TYPES.md](TYPES.md)
- **Examples**: [examples/](../examples/)
- **Issues**: [GitHub Issues](https://github.com/gmajor-encrypt/php-scale-codec/issues)

---

## Changelog Summary

See [CHANGELOG.md](CHANGELOG.md) for full details.

### Key Changes in v2.0

- **Minimum PHP version**: 8.2
- **New architecture**: Type-based API
- **TypeRegistry**: Centralized type management
- **ScaleBytes**: New byte container class
- **Metadata**: Support for v12-v15
- **Performance**: 2-3x faster encoding/decoding
- **Type safety**: Full PHPStan level 9 compatibility
