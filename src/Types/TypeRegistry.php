<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Exception\InvalidTypeException;

/**
 * Enhanced type registry for SCALE types.
 * 
 * Features:
 * - Type registration with optional override
 * - Type aliases
 * - Type caching for performance
 * - Frozen mode for immutability
 * - Metadata-based auto-registration
 */
class TypeRegistry
{
    /**
     * @var array<string, TypeInterface|callable> Registered types or type factories
     */
    private array $types = [];

    /**
     * @var array<string, string> Type aliases (alias => target)
     */
    private array $aliases = [];

    /**
     * @var array<string, TypeInterface> Type cache for resolved types
     */
    private array $cache = [];

    /**
     * @var bool Whether the registry is frozen (immutable)
     */
    private bool $frozen = false;

    /**
     * @var array<string, array> Metadata-based type definitions
     */
    private array $metadataTypes = [];

    /**
     * Create a new type registry with default types registered.
     */
    public function __construct()
    {
        $this->registerDefaultTypes();
    }

    /**
     * Register a type.
     *
     * @param string $name The type name
     * @param TypeInterface|callable $type The type instance or a factory callable
     * @param bool $allowOverride Whether to allow overriding an existing type
     * @throws InvalidTypeException If registry is frozen or type already registered (when override not allowed)
     */
    public function register(string $name, TypeInterface|callable $type, bool $allowOverride = false): void
    {
        $this->ensureNotFrozen();

        $normalizedName = $this->normalizeName($name);

        if (!$allowOverride && isset($this->types[$normalizedName])) {
            throw new InvalidTypeException(sprintf('Type "%s" is already registered. Use allowOverride=true to replace.', $name));
        }

        $this->types[$normalizedName] = $type;
        
        // Invalidate cache for this type
        unset($this->cache[$normalizedName]);
    }

    /**
     * Register multiple types at once.
     *
     * @param array<string, TypeInterface|callable> $types Map of name => type
     * @param bool $allowOverride Whether to allow overriding existing types
     */
    public function registerMany(array $types, bool $allowOverride = false): void
    {
        foreach ($types as $name => $type) {
            $this->register($name, $type, $allowOverride);
        }
    }

    /**
     * Register a type alias.
     *
     * @param string $alias The alias name
     * @param string $target The target type name
     */
    public function registerAlias(string $alias, string $target): void
    {
        $this->ensureNotFrozen();

        $normalizedAlias = $this->normalizeName($alias);
        $normalizedTarget = $this->normalizeName($target);

        $this->aliases[$normalizedAlias] = $normalizedTarget;
    }

    /**
     * Register multiple aliases at once.
     *
     * @param array<string, string> $aliases Map of alias => target
     */
    public function registerAliases(array $aliases): void
    {
        foreach ($aliases as $alias => $target) {
            $this->registerAlias($alias, $target);
        }
    }

    /**
     * Register types from metadata definition.
     *
     * @param array $metadataTypes The metadata type definitions
     * @param bool $allowOverride Whether to allow overriding existing types
     */
    public function registerFromMetadata(array $metadataTypes, bool $allowOverride = false): void
    {
        $this->ensureNotFrozen();

        foreach ($metadataTypes as $name => $definition) {
            $normalizedName = $this->normalizeName($name);
            $this->metadataTypes[$normalizedName] = $definition;
            
            // Will be resolved lazily when accessed
            $this->types[$normalizedName] = function () use ($normalizedName) {
                return $this->createTypeFromMetadata($normalizedName);
            };
        }
    }

    /**
     * Get a type by name.
     *
     * @param string $name The type name
     * @return TypeInterface The type instance
     * @throws InvalidTypeException If type is not registered
     */
    public function get(string $name): TypeInterface
    {
        $normalizedName = $this->normalizeName($name);

        // Resolve alias
        $normalizedName = $this->resolveAlias($normalizedName);

        // Check cache first
        if (isset($this->cache[$normalizedName])) {
            return clone $this->cache[$normalizedName];
        }

        // Check if type is registered
        if (!isset($this->types[$normalizedName])) {
            throw InvalidTypeException::notRegistered($name);
        }

        // Resolve type
        $type = $this->types[$normalizedName];

        // If it's a callable factory, invoke it
        if (is_callable($type)) {
            $type = $type();
            $this->types[$normalizedName] = $type;
        }

        // Cache the resolved type
        $this->cache[$normalizedName] = $type;

        return clone $type;
    }

    /**
     * Check if a type is registered.
     *
     * @param string $name The type name
     * @return bool True if the type is registered
     */
    public function has(string $name): bool
    {
        $normalizedName = $this->normalizeName($name);
        $normalizedName = $this->resolveAlias($normalizedName);

        return isset($this->types[$normalizedName]);
    }

    /**
     * Get all registered type names.
     *
     * @return array<string> List of type names
     */
    public function getRegisteredTypes(): array
    {
        return array_keys($this->types);
    }

    /**
     * Get all registered aliases.
     *
     * @return array<string, string> Map of alias => target
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Freeze the registry, making it immutable.
     */
    public function freeze(): void
    {
        $this->frozen = true;
    }

    /**
     * Unfreeze the registry, allowing modifications.
     */
    public function unfreeze(): void
    {
        $this->frozen = false;
    }

    /**
     * Check if the registry is frozen.
     *
     * @return bool True if frozen
     */
    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    /**
     * Clear the type cache.
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Create a new registry with the same types (shallow copy).
     *
     * @return self A new registry instance
     */
    public function clone(): self
    {
        $newRegistry = new self();
        $newRegistry->types = $this->types;
        $newRegistry->aliases = $this->aliases;
        $newRegistry->metadataTypes = $this->metadataTypes;
        $newRegistry->frozen = $this->frozen;
        return $newRegistry;
    }

    /**
     * Get metadata type definition.
     *
     * @param string $name The type name
     * @return array|null The metadata definition or null
     */
    public function getMetadataDefinition(string $name): ?array
    {
        $normalizedName = $this->normalizeName($name);
        return $this->metadataTypes[$normalizedName] ?? null;
    }

    /**
     * Ensure the registry is not frozen.
     *
     * @throws InvalidTypeException If the registry is frozen
     */
    private function ensureNotFrozen(): void
    {
        if ($this->frozen) {
            throw new InvalidTypeException('Cannot modify a frozen type registry');
        }
    }

    /**
     * Resolve an alias to its target type name.
     *
     * @param string $name The type name or alias
     * @return string The resolved type name
     */
    private function resolveAlias(string $name): string
    {
        $resolved = $name;
        $visited = [];

        while (isset($this->aliases[$resolved])) {
            if (isset($visited[$resolved])) {
                throw new InvalidTypeException(sprintf('Circular alias detected: %s', $name));
            }
            $visited[$resolved] = true;
            $resolved = $this->aliases[$resolved];
        }

        return $resolved;
    }

    /**
     * Normalize a type name for consistent lookup.
     *
     * @param string $name The type name
     * @return string The normalized name
     */
    private function normalizeName(string $name): string
    {
        return strtolower(trim($name));
    }

    /**
     * Create a type from metadata definition.
     *
     * @param string $name The type name
     * @return TypeInterface The created type
     */
    private function createTypeFromMetadata(string $name): TypeInterface
    {
        $definition = $this->metadataTypes[$name] ?? null;

        if ($definition === null) {
            throw new InvalidTypeException(sprintf('No metadata definition for type: %s', $name));
        }

        // Handle different type definitions
        // This is a simplified implementation - real implementation would handle full metadata
        $type = match ($definition['type'] ?? $definition[0] ?? null) {
            'struct' => $this->createStructFromMetadata($definition),
            'enum' => $this->createEnumFromMetadata($definition),
            'tuple' => $this->createTupleFromMetadata($definition),
            'sequence' => $this->createVecFromMetadata($definition),
            'array' => $this->createArrayFromMetadata($definition),
            'option' => $this->createOptionFromMetadata($definition),
            default => throw new InvalidTypeException(sprintf('Unknown type definition: %s', json_encode($definition))),
        };

        return $type;
    }

    /**
     * Create a struct type from metadata.
     */
    private function createStructFromMetadata(array $definition): StructType
    {
        $struct = new StructType($this);
        
        $fields = $definition['fields'] ?? $definition[1] ?? [];
        foreach ($fields as $field) {
            $name = $field['name'] ?? $field[0] ?? '';
            $type = $field['type'] ?? $field[1] ?? '';
            // Will be resolved lazily
            $struct->addField($name, $this->get($type));
        }

        return $struct;
    }

    /**
     * Create an enum type from metadata.
     */
    private function createEnumFromMetadata(array $definition): EnumType
    {
        $enum = new EnumType($this);
        
        $variants = $definition['variants'] ?? $definition[1] ?? [];
        foreach ($variants as $index => $variant) {
            $name = $variant['name'] ?? $variant[0] ?? '';
            $fields = $variant['fields'] ?? $variant[1] ?? null;
            $enum->addVariant($name, $index, $fields);
        }

        return $enum;
    }

    /**
     * Create a tuple type from metadata.
     */
    private function createTupleFromMetadata(array $definition): TupleType
    {
        $tuple = new TupleType($this);
        
        $types = $definition['types'] ?? $definition[1] ?? [];
        foreach ($types as $typeName) {
            $tuple->addElementType($this->get($typeName));
        }

        return $tuple;
    }

    /**
     * Create a Vec type from metadata.
     */
    private function createVecFromMetadata(array $definition): VecType
    {
        $vec = new VecType($this);
        $elementType = $definition['elementType'] ?? $definition[1] ?? 'U8';
        $vec->setElementType($this->get($elementType));
        return $vec;
    }

    /**
     * Create an array type from metadata.
     */
    private function createArrayFromMetadata(array $definition): FixedArrayType
    {
        $array = new FixedArrayType($this);
        $elementType = $definition['elementType'] ?? $definition[1] ?? 'U8';
        $length = $definition['length'] ?? $definition[2] ?? 0;
        $array->setElementType($this->get($elementType));
        $array->setLength($length);
        return $array;
    }

    /**
     * Create an option type from metadata.
     */
    private function createOptionFromMetadata(array $definition): OptionType
    {
        $option = new OptionType($this);
        $innerType = $definition['innerType'] ?? $definition[1] ?? 'U8';
        $option->setInnerType($this->get($innerType));
        return $option;
    }

    /**
     * Register default SCALE types.
     */
    private function registerDefaultTypes(): void
    {
        // Primitive types
        $this->register('bool', new BoolType($this));
        $this->register('null', new NullType($this));
        $this->register('u8', new U8($this));
        $this->register('u16', new U16($this));
        $this->register('u32', new U32($this));
        $this->register('u64', new U64($this));
        $this->register('u128', new U128($this));
        $this->register('i8', new I8($this));
        $this->register('i16', new I16($this));
        $this->register('i32', new I32($this));
        $this->register('i64', new I64($this));
        $this->register('i128', new I128($this));

        // Special types
        $this->register('compact', new Compact($this));
        $this->register('bytes', new BytesType($this));
        $this->register('string', new StringType($this));
        $this->register('text', new StringType($this));
        $this->register('accountid', new AccountIdType($this));
        $this->register('address', new MultiAddressType($this));
        $this->register('multiaddress', new MultiAddressType($this));

        // Compound types
        $this->register('vec', new VecType($this));
        $this->register('option', new OptionType($this));
        $this->register('tuple', new TupleType($this));
        $this->register('struct', new StructType($this));
        $this->register('enum', new EnumType($this));
        $this->register('result', new ResultType($this));
        $this->register('fixedarray', new FixedArrayType($this));

        // Type aliases
        $this->registerAliases([
            'boolean' => 'bool',
            'int' => 'i32',
            'uint' => 'u32',
            'str' => 'string',
        ]);
    }
}
