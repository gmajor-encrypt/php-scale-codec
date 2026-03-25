# polkadot.js Compatibility Tests

This directory contains compatibility tests between php-scale-codec and polkadot.js SCALE codec reference.

## Purpose

Ensure full compatibility between the PHP implementation and the JavaScript (polkadot.js) reference implementation of SCALE codec.

## Test Vectors

Test vectors are stored in `tests/test-vectors.json` and contain:

- Known input values
- Expected hex-encoded outputs from polkadot.js

## Running Tests

### PHP Compatibility Tests

```bash
php tests/php-compatibility-test.php
```

### Node.js Tests (requires polkadot.js installed)

```bash
npm install
npm test
```

## Test Categories

| Category | Types |
|----------|-------|
| Integers | U8, U16, U32, U64, U128, I8, I16, I32, I64 |
| Compact | Compact integers |
| Boolean | bool |
| String | Text, String |
| Collections | Vec, FixedArray |
| Option | Option<T> |
| Tuple | Tuples |
| Struct | Struct types |
| Enum | Enum types |

## Adding New Test Vectors

1. Generate test data with polkadot.js
2. Add to `tests/test-vectors.json`
3. Run compatibility tests
4. Fix any discrepancies

## Continuous Testing

These tests should be run:

1. Before every release
2. On CI pipeline (when workflow permissions allow)
3. After any changes to encoding/decoding logic

## Compatibility Report

Run tests to generate a compatibility report showing:

- Passed tests
- Failed tests (mismatches)
- Errors (exceptions)

## Related

- Issue #44
- polkadot.js: https://github.com/polkadot-js/api
- SCALE Codec Spec: https://docs.substrate.io/reference/scale-codec/
