<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * Unsigned 32-bit integer type.
 */
class U32 extends AbstractUintType
{
    protected int $byteSize = 4;
    protected int $maxValue = 4294967295;

    public function getTypeName(): string
    {
        return 'U32';
    }
}
