# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-03-30

### Added

- **Phase 1: Architecture Refactoring**
  - Complete directory structure reorganization
  - New core interfaces for better extensibility
  - Improved separation of concerns
  - **TypeRegistry**: Centralized type management and lookup
  - **ScaleBytes**: New byte container class with improved API
  - **TypeFactory**: Factory for creating parameterized types

- **Phase 2: Type System**
  - Basic integer types implementation (U8-U128, I8-I128)
  - Compact type encoding/decoding
  - Special types implementation (Bool, String, Bytes)
  - Compound types (Vec, Option, Tuple, Struct, Enum)
  - Custom type registration mechanism

- **Phase 3: Metadata Parser**
  - Refactored metadata parser for better maintainability
  - Metadata v12-v15 support
  - Event parsing: Parse and decode chain events
  - Extrinsic builder: Build and sign extrinsics

- **Phase 4: Quality & Testing**
  - Static analysis integration (PHPStan level 9)
  - Unit test coverage improvement (344 tests, 654 assertions)
  - Performance benchmark system (PHPBench integration)
  - polkadot.js compatibility tests: Ensure encoding compatibility

- **Phase 5: Documentation & Compatibility**
  - API documentation generation (phpDocumentor)
  - Usage guide and examples
  - Migration guide from v1.x to v2.0

### Changed

- **BREAKING**: Complete architecture refactoring
- **BREAKING**: Namespace changes: `Substrate\Scale\Codec` → `Substrate\ScaleCodec`
- **Minimum PHP version**: 8.2+ (from 7.4+)
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

### Requirements

- PHP 8.2+
- ext-gmp
- ext-json
- ext-sodium

## [1.x] - Previous Versions

See git history for previous releases.

---

## Migration Guide

See [MIGRATION.md](docs/MIGRATION.md) for detailed migration instructions from v1.x to v2.0.
