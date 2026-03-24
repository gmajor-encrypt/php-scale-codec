<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Metadata;

/**
 * Represents a pallet (module) in Substrate metadata.
 */
class Pallet
{
    /**
     * @param string $name Pallet name
     * @param int $index Pallet index
     * @param array $storage Storage entries
     * @param array $calls Call functions
     * @param array $events Event types
     * @param array $errors Error types
     * @param array $constants Constants
     */
    public function __construct(
        public readonly string $name,
        public readonly int $index,
        public readonly array $storage = [],
        public readonly array $calls = [],
        public readonly array $events = [],
        public readonly array $errors = [],
        public readonly array $constants = [],
    ) {}

    /**
     * Get a storage entry by name.
     */
    public function getStorage(string $name): ?array
    {
        foreach ($this->storage as $entry) {
            if (($entry['name'] ?? null) === $name) {
                return $entry;
            }
        }
        return null;
    }

    /**
     * Get a call by name.
     */
    public function getCall(string $name): ?array
    {
        foreach ($this->calls as $call) {
            if (($call['name'] ?? null) === $name) {
                return $call;
            }
        }
        return null;
    }

    /**
     * Get an event by index.
     */
    public function getEvent(int $index): ?array
    {
        return $this->events[$index] ?? null;
    }

    /**
     * Get an error by index.
     */
    public function getError(int $index): ?array
    {
        return $this->errors[$index] ?? null;
    }

    /**
     * Get a constant by name.
     */
    public function getConstant(string $name): ?array
    {
        foreach ($this->constants as $constant) {
            if (($constant['name'] ?? null) === $name) {
                return $constant;
            }
        }
        return null;
    }
}
