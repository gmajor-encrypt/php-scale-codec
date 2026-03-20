<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * Unsigned 8-bit integer type.
 */
class U8 extends AbstractUintType
{
    protected int $byteSize = 1;
    protected int|string $maxValue = 255;

    public function getTypeName(): string
    {
        return 'U8';
    }
}
