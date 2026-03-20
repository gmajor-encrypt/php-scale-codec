<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Exception\InvalidTypeException;
use Substrate\ScaleCodec\Bytes\ScaleBytes;

/**
 * Type registry for SCALE types.
 * 
 * Manages registration and retrieval of type instances.
 * Immutable after initialization for thread safety.
 */
class TypeRegistry
{
    /**
     * @var array<string, TypeInterface> Registered types
     */
    private array $types = [];

    /**
     * @var array<string, string> Type aliases
     */
    private array $aliases = [];

    /**
     * @var bool Whether the registry is frozen (immutable)
     */
    private bool $frozen = false;

    /**
     * Register a type.
     *
     * @param string $name The type name
     * @param TypeInterface $type The type instance
     * @throws InvalidTypeException If registry is frozen or type already registered
     */
    public function register(string $name, TypeInterface $type): void
    {
        if ($this->frozen) {
            throw new InvalidTypeException('Cannot register types on a frozen registry');
        }

        $normalizedName = $this->normalizeName($name);

        if (isset($this->types[$normalizedName])) {
            throw InvalidTypeException::notRegistered($name);
        }

        $this->types[$normalizedName] = $type;
    }

    /**
     * Register a type alias.
     *
     * @param string $alias The alias name
     * @param string $target The target type name
     */
    public function registerAlias(string $alias, string $target): void
    {
        if ($this->frozen) {
            throw new InvalidTypeException('Cannot register aliases on a frozen registry');
        }

        $this->aliases[$this->normalizeName($alias)] = $this->normalizeName($target);
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

        // Check if it's an alias
        if (isset($this->aliases[$normalizedName])) {
            $normalizedName = $this->aliases[$normalizedName];
        }

        if (!isset($this->types[$normalizedName])) {
            throw InvalidTypeException::notRegistered($name);
        }

        return clone $this->types[$normalizedName];
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

        if (isset($this->aliases[$normalizedName])) {
            $normalizedName = $this->aliases[$normalizedName];
        }

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
     * Freeze the registry, making it immutable.
     */
    public function freeze(): void
    {
        $this->frozen = true;
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
     * Create a new registry with the same types (shallow copy).
     *
     * @return self A new registry instance
     */
    public function clone(): self
    {
        $newRegistry = new self();
        $newRegistry->types = $this->types;
        $newRegistry->aliases = $this->aliases;
        return $newRegistry;
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
}
