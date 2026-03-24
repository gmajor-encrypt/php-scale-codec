<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Event;

/**
 * Represents an EventRecord from Substrate.
 * 
 * EventRecord contains:
 * - phase: The execution phase
 * - event: The actual event
 * - topics: List of topics (for indexing)
 */
class EventRecord
{
    /**
     * @param Phase $phase Execution phase
     * @param int|null $extrinsicIndex Extrinsic index (for ApplyExtrinsic phase)
     * @param Event $event The event
     * @param array $topics List of topic hashes
     */
    public function __construct(
        public readonly Phase $phase,
        public readonly ?int $extrinsicIndex,
        public readonly Event $event,
        public readonly array $topics = [],
    ) {}

    /**
     * Check if this is an ApplyExtrinsic phase.
     */
    public function isApplyExtrinsic(): bool
    {
        return $this->phase === Phase::ApplyExtrinsic;
    }

    /**
     * Check if this is a Finalization phase.
     */
    public function isFinalization(): bool
    {
        return $this->phase === Phase::Finalization;
    }

    /**
     * Check if this is an Initialization phase.
     */
    public function isInitialization(): bool
    {
        return $this->phase === Phase::Initialization;
    }

    /**
     * Get the extrinsic index (if in ApplyExtrinsic phase).
     */
    public function getExtrinsicIndex(): ?int
    {
        return $this->phase === Phase::ApplyExtrinsic ? $this->extrinsicIndex : null;
    }

    /**
     * Check if this event has topics.
     */
    public function hasTopics(): bool
    {
        return !empty($this->topics);
    }

    /**
     * Convert to array representation.
     */
    public function toArray(): array
    {
        return [
            'phase' => $this->phase->value,
            'extrinsicIndex' => $this->extrinsicIndex,
            'event' => $this->event->toArray(),
            'topics' => $this->topics,
        ];
    }
}
