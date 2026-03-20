<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * Signed 32-bit integer type.
 */
class I32 extends AbstractIntType
{
    protected int $byteSize = 4;
    protected int $minValue = -2147483648;
    protected int $maxValue = 2147483647;

    public function getTypeName(): string
    {
        return 'I32';
    }
}
