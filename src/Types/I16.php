<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * Signed 16-bit integer type.
 */
class I16 extends AbstractIntType
{
    protected int $byteSize = 2;
    protected int|string $minValue = -32768;
    protected int|string $maxValue = 32767;

    public function getTypeName(): string
    {
        return 'I16';
    }
}
