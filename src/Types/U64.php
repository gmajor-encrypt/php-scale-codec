<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * Unsigned 64-bit integer type.
 */
class U64 extends AbstractUintType
{
    protected int $byteSize = 8;
    protected int|string $maxValue = '18446744073709551615';

    public function getTypeName(): string
    {
        return 'U64';
    }

    public function decode(\Substrate\ScaleCodec\Bytes\ScaleBytes $bytes): string|int
    {
        $data = $bytes->readBytes(8); // Read 64-bit (8 bytes)
        $value = '0'; // Start decoding as string for large values

        foreach ($data as $byte) {
            $value = bcmul($value, '256');
            $value = bcadd($value, (string)$byte);
        }

        return $value;
    }
}
