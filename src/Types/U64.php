<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * Unsigned 64-bit integer type.
 */
class U64 extends AbstractUintType
{
    protected int $byteSize = 8;
    protected int $maxValue = 18446744073709551615;

    public function getTypeName(): string
    {
        return 'U64';
    }
}
