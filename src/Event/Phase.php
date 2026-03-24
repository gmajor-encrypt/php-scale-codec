<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Event;

/**
 * Represents a phase in block execution.
 */
enum Phase: string
{
    case ApplyExtrinsic = 'ApplyExtrinsic';
    case Finalization = 'Finalization';
    case Initialization = 'Initialization';

    /**
     * Create from index.
     */
    public static function fromIndex(int $index): self
    {
        return match ($index) {
            0 => self::ApplyExtrinsic,
            1 => self::Finalization,
            2 => self::Initialization,
            default => self::ApplyExtrinsic,
        };
    }

    /**
     * Get the index value.
     */
    public function toIndex(): int
    {
        return match ($this) {
            self::ApplyExtrinsic => 0,
            self::Finalization => 1,
            self::Initialization => 2,
        };
    }
}
