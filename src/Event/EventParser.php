<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Event;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Metadata\Metadata;
use Substrate\ScaleCodec\Types\{TypeRegistry, Compact};

/**
 * Parser for Substrate events.
 */
class EventParser
{
    private TypeRegistry $registry;
    private ?Metadata $metadata;

    /**
     * Create a new event parser.
     *
     * @param TypeRegistry|null $registry Type registry
     * @param Metadata|null $metadata Metadata for event definitions
     */
    public function __construct(?TypeRegistry $registry = null, ?Metadata $metadata = null)
    {
        $this->registry = $registry ?? new TypeRegistry();
        $this->metadata = $metadata;
    }

    /**
     * Parse a single EventRecord from bytes.
     *
     * @param ScaleBytes $bytes The encoded bytes
     * @return EventRecord The parsed event record
     */
    public function parseEventRecord(ScaleBytes $bytes): EventRecord
    {
        // Parse phase
        $phase = $this->parsePhase($bytes);

        // Parse event
        $event = $this->parseEvent($bytes);

        // Parse topics (Vec<[u8; 32]>)
        $topics = $this->parseTopics($bytes);

        return new EventRecord(
            phase: $phase['phase'],
            extrinsicIndex: $phase['extrinsicIndex'],
            event: $event,
            topics: $topics,
        );
    }

    /**
     * Parse multiple EventRecords from bytes.
     *
     * @param ScaleBytes $bytes The encoded bytes
     * @return array<EventRecord> Array of event records
     */
    public function parseEventRecords(ScaleBytes $bytes): array
    {
        $compact = new Compact($this->registry);
        $count = $compact->decode($bytes);

        $records = [];
        for ($i = 0; $i < $count; $i++) {
            $records[] = $this->parseEventRecord($bytes);
        }

        return $records;
    }

    /**
     * Parse hex string to EventRecords.
     *
     * @param string $hex Hex string (with or without 0x prefix)
     * @return array<EventRecord> Array of event records
     */
    public function parseHex(string $hex): array
    {
        if (!str_starts_with($hex, '0x')) {
            $hex = '0x' . $hex;
        }
        return $this->parseEventRecords(ScaleBytes::fromHex($hex));
    }

    /**
     * Parse the execution phase.
     */
    private function parsePhase(ScaleBytes $bytes): array
    {
        $variant = $bytes->readByte();

        if ($variant === 0) {
            // ApplyExtrinsic(u32)
            $extrinsicIndex = $this->readU32($bytes);
            return [
                'phase' => Phase::ApplyExtrinsic,
                'extrinsicIndex' => $extrinsicIndex,
            ];
        }

        if ($variant === 1) {
            // Finalization
            return [
                'phase' => Phase::Finalization,
                'extrinsicIndex' => null,
            ];
        }

        if ($variant === 2) {
            // Initialization
            return [
                'phase' => Phase::Initialization,
                'extrinsicIndex' => null,
            ];
        }

        throw new \RuntimeException("Unknown phase variant: $variant");
    }

    /**
     * Parse the event.
     */
    private function parseEvent(ScaleBytes $bytes): Event
    {
        // Pallet index
        $palletIndex = $bytes->readByte();

        // Event index within pallet
        $eventIndex = $bytes->readByte();

        // Try to get event info from metadata
        $palletName = $this->getPalletName($palletIndex);
        $eventName = $this->getEventName($palletIndex, $eventIndex);

        // Parse event data (remaining bytes until next structure)
        // In a real implementation, we'd use metadata to decode properly
        $data = $this->parseEventData($bytes, $palletIndex, $eventIndex);

        return new Event(
            pallet: $palletName,
            name: $eventName,
            palletIndex: $palletIndex,
            eventIndex: $eventIndex,
            data: $data,
        );
    }

    /**
     * Parse event data fields.
     */
    private function parseEventData(ScaleBytes $bytes, int $palletIndex, int $eventIndex): array
    {
        // In a real implementation, we'd use metadata to determine field types
        // For now, return raw bytes or skip
        // This would require integration with Metadata to properly decode

        // If we have metadata, try to decode fields
        if ($this->metadata !== null) {
            $pallet = $this->metadata->getPalletByIndex($palletIndex);
            if ($pallet !== null) {
                // Would decode based on event definition
                // For now, just return empty
            }
        }

        // Fallback: return remaining bytes as raw data
        // Note: this is not correct for real usage
        return [];
    }

    /**
     * Parse topics.
     */
    private function parseTopics(ScaleBytes $bytes): array
    {
        $compact = new Compact($this->registry);
        $count = $compact->decode($bytes);

        $topics = [];
        for ($i = 0; $i < $count; $i++) {
            // Each topic is [u8; 32]
            $topicBytes = $bytes->readBytes(32);
            $topics[] = '0x' . bin2hex(pack('C*', ...$topicBytes));
        }

        return $topics;
    }

    /**
     * Get pallet name by index.
     */
    private function getPalletName(int $index): string
    {
        if ($this->metadata !== null) {
            $pallet = $this->metadata->getPalletByIndex($index);
            if ($pallet !== null) {
                return $pallet->name;
            }
        }

        return "Pallet_$index";
    }

    /**
     * Get event name by indices.
     */
    private function getEventName(int $palletIndex, int $eventIndex): string
    {
        if ($this->metadata !== null) {
            $pallet = $this->metadata->getPalletByIndex($palletIndex);
            if ($pallet !== null) {
                $event = $pallet->getEvent($eventIndex);
                if ($event !== null && isset($event['name'])) {
                    return $event['name'];
                }
            }
        }

        return "Event_$eventIndex";
    }

    /**
     * Read U32 little-endian.
     */
    private function readU32(ScaleBytes $bytes): int
    {
        $b = $bytes->readBytes(4);
        return $b[0] | ($b[1] << 8) | ($b[2] << 16) | ($b[3] << 24);
    }
}
