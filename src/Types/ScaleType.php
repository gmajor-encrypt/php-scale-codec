<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * SCALE type enumeration.
 * Defines all supported SCALE types with PHP 8.2 enum.
 */
enum ScaleType: string
{
    // Unsigned integers
    case U8 = 'u8';
    case U16 = 'u16';
    case U32 = 'u32';
    case U64 = 'u64';
    case U128 = 'u128';
    case U256 = 'u256';

    // Signed integers
    case I8 = 'i8';
    case I16 = 'i16';
    case I32 = 'i32';
    case I64 = 'i64';
    case I128 = 'i128';
    case I256 = 'i256';

    // Basic types
    case Bool = 'bool';
    case String = 'string';
    case Bytes = 'bytes';
    case Null = 'null';

    // Compound types
    case Compact = 'compact';
    case Option = 'option';
    case Result = 'result';
    case Vec = 'vec';
    case Tuple = 'tuple';
    case Struct = 'struct';
    case Enum = 'enum';
    case Set = 'set';
    case BTreeMap = 'btreemap';

    // Special types
    case Address = 'address';
    case AccountId = 'accountid';
    case MultiAddress = 'multiaddress';
    case H256 = 'h256';
    case H512 = 'h512';
    case Era = 'era';
    case BitVec = 'bitvec';

    // Fixed array
    case FixedArray = 'fixedarray';

    /**
     * Check if this is an unsigned integer type.
     */
    public function isUnsignedInt(): bool
    {
        return match ($this) {
            self::U8, self::U16, self::U32, self::U64, self::U128, self::U256 => true,
            default => false,
        };
    }

    /**
     * Check if this is a signed integer type.
     */
    public function isSignedInt(): bool
    {
        return match ($this) {
            self::I8, self::I16, self::I32, self::I64, self::I128, self::I256 => true,
            default => false,
        };
    }

    /**
     * Check if this is an integer type (signed or unsigned).
     */
    public function isInteger(): bool
    {
        return $this->isUnsignedInt() || $this->isSignedInt();
    }

    /**
     * Get the byte size for integer types.
     * Returns null for non-integer types.
     */
    public function getByteSize(): ?int
    {
        return match ($this) {
            self::U8, self::I8 => 1,
            self::U16, self::I16 => 2,
            self::U32, self::I32 => 4,
            self::U64, self::I64 => 8,
            self::U128, self::I128 => 16,
            self::U256, self::I256 => 32,
            default => null,
        };
    }

    /**
     * Get the bit size for integer types.
     */
    public function getBitSize(): ?int
    {
        $byteSize = $this->getByteSize();
        return $byteSize !== null ? $byteSize * 8 : null;
    }

    /**
     * Parse a type string and return the corresponding ScaleType.
     *
     * @param string $typeString The type string to parse
     * @return ScaleType|null The corresponding ScaleType or null if not a base type
     */
    public static function fromTypeString(string $typeString): ?self
    {
        $normalized = strtolower(trim($typeString));
        
        // Handle common aliases
        $aliases = [
            'int' => self::I32,
            'uint' => self::U32,
            'balance' => self::U128,
            'blocknumber' => self::U32,
            'index' => self::U32,
            'hash' => self::H256,
        ];

        if (isset($aliases[$normalized])) {
            return $aliases[$normalized];
        }

        return self::tryFrom($normalized);
    }
}
