<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * Signed 64-bit integer type.
 */
class I64 extends AbstractIntType
{
    protected int $byteSize = 8;
    protected int|string $minValue = '-9223372036854775808';
    protected int|string $maxValue = '9223372036854775807';

    public function getTypeName(): string
    {
        return 'I64';
    }
}
