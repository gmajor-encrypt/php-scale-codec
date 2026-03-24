<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Metadata;

/**
 * Metadata version enum.
 */
enum MetadataVersion: int
{
    case V12 = 12;
    case V13 = 13;
    case V14 = 14;
    case V15 = 15;

    /**
     * Create from integer value.
     */
    public static function fromInt(int $version): ?self
    {
        return match ($version) {
            12 => self::V12,
            13 => self::V13,
            14 => self::V14,
            15 => self::V15,
            default => null,
        };
    }

    /**
     * Check if this version supports portable types.
     */
    public function supportsPortableTypes(): bool
    {
        return $this->value >= 14;
    }

    /**
     * Check if this version supports apis field.
     */
    public function supportsApis(): bool
    {
        return $this->value >= 15;
    }
}
