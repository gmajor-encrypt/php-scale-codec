# php-scale-codec

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777bb4)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

PHP implementation of the **SCALE (Simple Concatenated Aggregate Little-Endian)** codec used in Substrate-based blockchains like Polkadot, Kusama, and Substrate-native chains.

## Features

- ✅ Full SCALE codec implementation
- ✅ All primitive types (U8-U128, I8-I128, Bool, String)
- ✅ Compact integer encoding
- ✅ Compound types (Vec, Option, Tuple, Struct, Enum)
- ✅ Metadata v12-v15 support
- ✅ Extrinsic building and signing
- ✅ Event parsing
- ✅ polkadot.js compatible

## Installation

```bash
composer require gmajor/substrate-codec-php
```

## Requirements

- PHP 8.2+
- ext-gmp (for large integer handling)
- ext-json
- ext-sodium

## Quick Start

### Basic Encoding/Decoding

```php
<?php

require_once 'vendor/autoload.php';

use Substrate\ScaleCodec\Types\TypeRegistry;
use Substrate\ScaleCodec\Bytes\ScaleBytes;

// Create type registry
$registry = new TypeRegistry();

// Encode a U32 value
$u32 = $registry->get('U32');
$encoded = $u32->encode(12345);
echo $encoded->toHex(); // 0x39300000

// Decode it back
$bytes = ScaleBytes::fromHex('0x39300000');
$decoded = $u32->decode($bytes);
echo $decoded; // 12345
```

### Compact Integers

```php
$compact = $registry->get('Compact');

// Small values (0-63): 1 byte
echo $compact->encode(42)->toHex(); // 0xa8

// Medium values (64-16383): 2 bytes
echo $compact->encode(100)->toHex(); // 0x9101

// Large values
echo $compact->encode(1000000)->toHex(); // 0x02c0843d00
```

### Boolean

```php
$bool = $registry->get('Bool');

$encoded = $bool->encode(true);  // 0x01
$encoded = $bool->encode(false); // 0x00
```

### String/Text

```php
$string = $registry->get('String');
$encoded = $string->encode('Hello, Substrate!');
// 0x2048656c6c6f2c2053756273747261746521
```

### Vectors

```php
// Vec<U8>
$vecU8 = (new VecType($registry))->setElementType($registry->get('U8'));
$encoded = $vecU8->encode([1, 2, 3, 4, 5]);
// 0x140102030405

$decoded = $vecU8->decode(ScaleBytes::fromHex('0x140102030405'));
// [1, 2, 3, 4, 5]
```

### Options

```php
$optionU32 = (new OptionType($registry))->setInnerType($registry->get('U32'));

// Some value
$encoded = $optionU32->encode(42); // 0x012a000000

// None value
$encoded = $optionU32->encode(null); // 0x00
```

### Structs

```php
$struct = (new StructType($registry))->setFields([
    'id' => $registry->get('U32'),
    'name' => $registry->get('String'),
    'active' => $registry->get('Bool'),
]);

$encoded = $struct->encode([
    'id' => 1,
    'name' => 'Alice',
    'active' => true,
]);
```

## Advanced Usage

### Metadata Parsing

```php
use Substrate\ScaleCodec\Metadata\MetadataParser;

$parser = new MetadataParser();
$metadata = $parser->parse($metadataHex);

// Access pallets
$systemPallet = $metadata->getPallet('System');

// Get events
$events = $metadata->getPalletEvents('Balances');
```

### Extrinsic Building

```php
use Substrate\ScaleCodec\Extrinsic\ExtrinsicBuilder;

$builder = new ExtrinsicBuilder($registry);
$extrinsic = $builder
    ->setVersion(4)
    ->setPallet('Balances')
    ->setFunction('transfer')
    ->setArgs([
        'dest' => $accountId,
        'value' => 1000000000,
    ])
    ->sign($keypair)
    ->build();
```

### Working with Large Integers

U64 and U128 return strings for values exceeding PHP_INT_MAX:

```php
$u64 = $registry->get('U64');

// Max U64 value
$encoded = $u64->encode('18446744073709551615');
$decoded = $u64->decode($bytes);
echo $decoded; // '18446744073709551615' (string)
```

## Documentation

- [API Reference](docs/API.md) - Complete API documentation
- [Types Reference](docs/TYPES.md) - Detailed type documentation
- [Static Analysis](docs/STATIC_ANALYSIS.md) - PHPStan configuration

## Testing

```bash
# Run unit tests
make test

# Run with coverage
make coverage

# Run compatibility tests
make compat

# Run static analysis
make stan

# Run code style check
make sniff
```

## Compatibility

This library maintains compatibility with [polkadot.js SCALE codec](https://polkadot.js.org/docs/api/). Run compatibility tests:

```bash
php compat/tests/php-compatibility-test.php
```

## Benchmarks

```bash
make bench        # Standard benchmarks
make bench-quick  # Quick benchmarks
make bench-full   # Full benchmarks
```

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Run tests: `make test`
4. Run static analysis: `make stan`
5. Run code style: `make sniff`
6. Commit changes: `git commit -am "feat: my feature"`
7. Push and create a PR

## License

MIT License. See [LICENSE](LICENSE) for details.

## Related

- [SCALE Codec Specification](https://docs.substrate.io/reference/scale-codec/)
- [Substrate Documentation](https://docs.substrate.io/)
- [polkadot.js API](https://polkadot.js.org/docs/api/)
