<?php

namespace Codec;

class ScaleBytes
{
    /**
     * @var array $data
     */
    public $data;

    /**
     * @var integer $offset
     */
    public $offset;

    /**
     * ScaleBytes constructor.
     * @param string|array $hexData
     */
    public function __construct($hexData)
    {
        if (is_string($hexData)) {
            $data = ctype_xdigit($hexData);
            if ($data === false) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a hex string', $hexData));
            }
            $this->data = Utils::hexToBytes($hexData);
        } elseif (is_array($hexData)) {
            $this->data = $hexData;
        } else {
            throw new \InvalidArgumentException(sprintf('"%s" not support for ScaleBytes', gettype($hexData)));
        }
    }

    /**
     * @param $length
     * @return array
     */
    public function nextBytes($length)
    {
        $data = array_slice($this->data, $this->offset, $length);
        $this->offset = $this->offset + $length;
        return $data;
    }

    /**
     * reset ScaleBytes
     */
    protected function reset()
    {
        $this->offset = 0;
    }

    /**
     * remainBytesLength
     * @return int
     */
    protected function remainBytesLength()
    {
        return count($this->data) - $this->offset;
    }
}
