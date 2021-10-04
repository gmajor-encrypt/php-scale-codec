<?php

namespace Codec\Types;

use Codec\Generator;

/**
 * Class StorageHasher
 * @package Codec\Types
 *
 * StorageHasher is enum, it include Multiple hashes
 */
class StorageHasher extends Enum
{
    public function __construct (Generator $generator)
    {
        parent::__construct($generator);
        $this->valueList = ["Blake2_128", "Blake2_256", "Blake2_128Concat", "Twox128", "Twox256", "Twox64Concat", "Identity"];
    }
}