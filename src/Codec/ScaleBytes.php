<?php

namespace Codec;

use InvalidArgumentException;

class ScaleBytes
{
    /**
     * ScaleBytes data
     * @var array $data
     */
    public array $data;

    /**
     * current decode offset
     * @var int $offset
     */
    public int $offset;

    /**
     * ScaleBytes constructor.
     *
     * @param string|array $hexData
     */
    public function __construct ($hexData)
    {
        // check param is string
        if (is_string($hexData)) {
            $hexData = Utils::trimHex($hexData);
            // check param is hex string
            $data = ctype_xdigit($hexData);
            if ($data === false) {
                throw new InvalidArgumentException(sprintf('"%s" is not a hex string', $hexData));
            }
            $this->data = Utils::hexToBytes($hexData);
            $this->offset = 0;
            // check param is byte array
        } elseif (is_array($hexData)) {
            $this->data = $hexData;
            $this->offset = 0;
        } else {
            throw new InvalidArgumentException(sprintf('"%s" not support for ScaleBytes', gettype($hexData)));
        }
    }

    /**
     *
     * get next param length bytes
     * @param $length
     * @return array
     */
    public function nextBytes ($length): array
    {
        $data = array_slice($this->data, $this->offset, $length);
        $this->offset = $this->offset + $length;
        return array_pad($data, $length, 0);
    }

    /**
     * reset offset if need
     * reset ScaleBytes
     */
    protected function reset ()
    {
        $this->offset = 0;
    }

    /**
     * get remain bytes length
     * remainBytesLength
     *
     * @return int
     */
    public function remainBytesLength (): int
    {
        return count($this->data) - $this->offset;
    }
}
