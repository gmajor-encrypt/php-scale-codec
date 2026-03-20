<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * Signed 8-bit integer type.
 */
class I8 extends AbstractIntType
{
    protected int $byteSize = 1;
    protected int $minValue = -128;
    protected int $maxValue = 127;

    public function getTypeName(): string
    {
        return 'I8';
    }
}
