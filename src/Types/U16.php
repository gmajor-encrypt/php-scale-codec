<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * Unsigned 16-bit integer type.
 */
class U16 extends AbstractUintType
{
    protected int $byteSize = 2;
    protected int|string $maxValue = 65535;

    public function getTypeName(): string
    {
        return 'U16';
    }
}
