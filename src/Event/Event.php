<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Event;

/**
 * Represents a Substrate event.
 */
class Event
{
    /**
     * @param string $pallet Pallet name
     * @param string $name Event name
     * @param int $palletIndex Pallet index
     * @param int $eventIndex Event index within pallet
     * @param array $data Event data fields
     */
    public function __construct(
        public readonly string $pallet,
        public readonly string $name,
        public readonly int $palletIndex,
        public readonly int $eventIndex,
        public readonly array $data = [],
    ) {}

    /**
     * Get the event identifier (pallet_index, event_index).
     */
    public function getIdentifier(): string
    {
        return "{$this->pallet}.{$this->name}";
    }

    /**
     * Get the event data by field name.
     */
    public function getField(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Check if the event has a specific field.
     */
    public function hasField(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Convert to array representation.
     */
    public function toArray(): array
    {
        return [
            'pallet' => $this->pallet,
            'name' => $this->name,
            'palletIndex' => $this->palletIndex,
            'eventIndex' => $this->eventIndex,
            'data' => $this->data,
        ];
    }
}
