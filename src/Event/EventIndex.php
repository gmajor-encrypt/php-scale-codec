<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Event;

/**
 * Utility for indexing and searching events.
 */
class EventIndex
{
    /**
     * @var array<EventRecord> Indexed events
     */
    private array $events = [];

    /**
     * @var array<string, array<int>> Index: pallet.name => [event indices]
     */
    private array $nameIndex = [];

    /**
     * @var array<int, array<int>> Index: pallet index => [event indices]
     */
    private array $palletIndex = [];

    /**
     * Create an event index from event records.
     *
     * @param array<EventRecord> $events Event records to index
     */
    public function __construct(array $events = [])
    {
        foreach ($events as $index => $event) {
            $this->addEvent($event, $index);
        }
    }

    /**
     * Add an event to the index.
     */
    public function addEvent(EventRecord $event, int $index): void
    {
        $this->events[$index] = $event;

        // Index by name
        $key = $event->event->getIdentifier();
        if (!isset($this->nameIndex[$key])) {
            $this->nameIndex[$key] = [];
        }
        $this->nameIndex[$key][] = $index;

        // Index by pallet
        $palletIndex = $event->event->palletIndex;
        if (!isset($this->palletIndex[$palletIndex])) {
            $this->palletIndex[$palletIndex] = [];
        }
        $this->palletIndex[$palletIndex][] = $index;
    }

    /**
     * Find events by pallet and event name.
     *
     * @param string $pallet Pallet name
     * @param string $eventName Event name
     * @return array<EventRecord> Matching events
     */
    public function findByName(string $pallet, string $eventName): array
    {
        $key = "$pallet.$eventName";
        $indices = $this->nameIndex[$key] ?? [];
        return array_map(fn($i) => $this->events[$i], $indices);
    }

    /**
     * Find events by pallet index.
     *
     * @param int $palletIndex Pallet index
     * @return array<EventRecord> Matching events
     */
    public function findByPallet(int $palletIndex): array
    {
        $indices = $this->palletIndex[$palletIndex] ?? [];
        return array_map(fn($i) => $this->events[$i], $indices);
    }

    /**
     * Find events in ApplyExtrinsic phase.
     *
     * @return array<EventRecord> Events in ApplyExtrinsic phase
     */
    public function findApplyExtrinsic(): array
    {
        return array_filter($this->events, fn($e) => $e->isApplyExtrinsic());
    }

    /**
     * Find events in Finalization phase.
     *
     * @return array<EventRecord> Events in Finalization phase
     */
    public function findFinalization(): array
    {
        return array_filter($this->events, fn($e) => $e->isFinalization());
    }

    /**
     * Find events by extrinsic index.
     *
     * @param int $extrinsicIndex Extrinsic index
     * @return array<EventRecord> Events from the specified extrinsic
     */
    public function findByExtrinsic(int $extrinsicIndex): array
    {
        return array_filter($this->events, fn($e) => $e->getExtrinsicIndex() === $extrinsicIndex);
    }

    /**
     * Get the first event matching criteria.
     *
     * @param string $pallet Pallet name
     * @param string $eventName Event name
     * @return EventRecord|null The first matching event or null
     */
    public function findFirst(string $pallet, string $eventName): ?EventRecord
    {
        $events = $this->findByName($pallet, $eventName);
        return $events[0] ?? null;
    }

    /**
     * Get all events.
     *
     * @return array<EventRecord> All events
     */
    public function all(): array
    {
        return $this->events;
    }

    /**
     * Get event count.
     */
    public function count(): int
    {
        return count($this->events);
    }

    /**
     * Check if an event exists.
     *
     * @param string $pallet Pallet name
     * @param string $eventName Event name
     */
    public function has(string $pallet, string $eventName): bool
    {
        $key = "$pallet.$eventName";
        return !empty($this->nameIndex[$key]);
    }

    /**
     * Get unique event identifiers.
     *
     * @return array<string> Unique event identifiers
     */
    public function getUniqueEventIds(): array
    {
        return array_keys($this->nameIndex);
    }
}
