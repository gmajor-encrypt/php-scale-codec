<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * Signed 128-bit integer type.
 */
class I128 extends AbstractIntType
{
    protected int $byteSize = 16;
    protected int|string $minValue = '-170141183460469231731687303715884105728';
    protected int|string $maxValue = '170141183460469231731687303715884105727';

    public function getTypeName(): string
    {
        return 'I128';
    }
}
