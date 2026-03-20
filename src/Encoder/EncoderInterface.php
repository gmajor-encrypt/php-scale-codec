<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Encoder;

use Substrate\ScaleCodec\Bytes\ScaleBytes;

/**
 * Interface for SCALE encoders.
 * 
 * All SCALE type encoders must implement this interface.
 */
interface EncoderInterface
{
    /**
     * Encode a value to SCALE bytes.
     *
     * @param mixed $value The value to encode
     * @return ScaleBytes The encoded bytes
     * @throws \Substrate\ScaleCodec\Exception\ScaleEncodeException When encoding fails
     */
    public function encode(mixed $value): ScaleBytes;

    /**
     * Check if this encoder can handle the given value.
     *
     * @param mixed $value The value to check
     * @return bool True if this encoder can handle the value
     */
    public function canEncode(mixed $value): bool;
}
