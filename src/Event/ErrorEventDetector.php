<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Event;

/**
 * Helper for detecting and handling error events.
 */
class ErrorEventDetector
{
    /**
     * Common error event patterns.
     */
    private const ERROR_EVENTS = [
        ['pallet' => 'System', 'name' => 'ExtrinsicFailed'],
        ['pallet' => 'System', 'name' => 'CodeNotFound'],
        ['pallet' => 'System', 'name' => 'InvalidSpecName'],
        ['pallet' => 'System', 'name' => 'SpecVersionNeeded'],
    ];

    /**
     * @var array<EventRecord> Events
     */
    private array $events;

    /**
     * Create an error detector from event records.
     *
     * @param array<EventRecord> $events Event records
     */
    public function __construct(array $events)
    {
        $this->events = $events;
    }

    /**
     * Check if there are any error events.
     */
    public function hasErrors(): bool
    {
        foreach (self::ERROR_EVENTS as $pattern) {
            foreach ($this->events as $event) {
                if ($event->event->pallet === $pattern['pallet'] 
                    && $event->event->name === $pattern['name']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get all error events.
     *
     * @return array<EventRecord> Error events
     */
    public function getErrors(): array
    {
        $errors = [];
        foreach (self::ERROR_EVENTS as $pattern) {
            foreach ($this->events as $event) {
                if ($event->event->pallet === $pattern['pallet'] 
                    && $event->event->name === $pattern['name']) {
                    $errors[] = $event;
                }
            }
        }
        return $errors;
    }

    /**
     * Check if a specific extrinsic failed.
     *
     * @param int $extrinsicIndex Extrinsic index
     */
    public function extrinsicFailed(int $extrinsicIndex): bool
    {
        foreach ($this->events as $event) {
            if ($event->event->pallet === 'System' 
                && $event->event->name === 'ExtrinsicFailed'
                && $event->getExtrinsicIndex() === $extrinsicIndex) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the first ExtrinsicFailed event for an extrinsic.
     *
     * @param int $extrinsicIndex Extrinsic index
     * @return EventRecord|null The error event or null
     */
    public function getExtrinsicError(int $extrinsicIndex): ?EventRecord
    {
        foreach ($this->events as $event) {
            if ($event->event->pallet === 'System' 
                && $event->event->name === 'ExtrinsicFailed'
                && $event->getExtrinsicIndex() === $extrinsicIndex) {
                return $event;
            }
        }
        return null;
    }

    /**
     * Get error summary for a block.
     *
     * @return array<string, int> Map of error identifiers to counts
     */
    public function getErrorSummary(): array
    {
        $summary = [];
        foreach ($this->events as $event) {
            $id = $event->event->getIdentifier();
            foreach (self::ERROR_EVENTS as $pattern) {
                if ($event->event->pallet === $pattern['pallet'] 
                    && $event->event->name === $pattern['name']) {
                    if (!isset($summary[$id])) {
                        $summary[$id] = 0;
                    }
                    $summary[$id]++;
                }
            }
        }
        return $summary;
    }
}
