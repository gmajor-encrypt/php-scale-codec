<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

/**
 * Unsigned 128-bit integer type.
 */
class U128 extends AbstractUintType
{
    protected int $byteSize = 16;
    protected string $maxValue = '340282366920938463463374607431768211455';

    public function getTypeName(): string
    {
        return 'U128';
    }
}
