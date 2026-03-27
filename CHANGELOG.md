# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-03-XX

### Added

- **TypeRegistry**: Centralized type management and lookup
- **ScaleBytes**: New byte container class with improved API
- **TypeFactory**: Factory for creating parameterized types
- **Metadata v15 support**: Full support for latest Substrate metadata
- **Event parsing**: Parse and decode chain events
- **Extrinsic builder**: Build and sign extrinsics
- **Compact integers**: Variable-length integer encoding
- **PHPStan level 9**: Full static analysis support
- **Comprehensive test suite**: >90% code coverage
- **Performance benchmarks**: PHPBench integration
- **polkadot.js compatibility tests**: Ensure encoding compatibility

### Changed

- **Minimum PHP version**: 8.2+ (from 7.4+)
- **Namespace**: `Substrate\Scale\Codec` → `Substrate\ScaleCodec`
- **Encoding API**: Direct type method calls instead of Codec class
- **Decoding API**: Uses ScaleBytes for input handling
- **Large integers**: Returns string for U64/U128 values > PHP_INT_MAX
- **Metadata API**: `getModule()` → `getPallet()`

### Removed

- **Codec class**: Replaced with TypeRegistry
- **Direct type instantiation**: Use TypeRegistry::get()
- **Legacy encode/decode methods**: Updated to new API

### Fixed

- U64/U128 integer overflow issues
- Little-endian byte order handling
- Metadata parsing for v14-v15
- Compact integer encoding edge cases

## [1.x] - Previous Versions

See git history for previous releases.

---

## Migration Guide

See [MIGRATION.md](docs/MIGRATION.md) for detailed migration instructions.
