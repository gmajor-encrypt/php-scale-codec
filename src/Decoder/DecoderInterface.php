<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Decoder;

use Substrate\ScaleCodec\Bytes\ScaleBytes;

/**
 * Interface for SCALE decoders.
 * 
 * All SCALE type decoders must implement this interface.
 */
interface DecoderInterface
{
    /**
     * Decode SCALE bytes to a PHP value.
     *
     * @param ScaleBytes $bytes The bytes to decode
     * @return mixed The decoded value
     * @throws \Substrate\ScaleCodec\Exception\ScaleDecodeException When decoding fails
     */
    public function decode(ScaleBytes $bytes): mixed;

    /**
     * Peek at the next value without advancing the byte pointer.
     *
     * @param ScaleBytes $bytes The bytes to peek from
     * @return mixed The peeked value
     */
    public function peek(ScaleBytes $bytes): mixed;
}
