<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * Result<Ok, Err> type implementation.
 * 
 * Represents either success (Ok) or failure (Err).
 * Encoded as:
 * - Ok(value): 0x00 + encoded_ok_value
 * - Err(value): 0x01 + encoded_err_value
 */
class ResultType extends AbstractType
{
    /**
     * @var TypeInterface|null The Ok type
     */
    protected ?TypeInterface $okType = null;

    /**
     * @var TypeInterface|null The Err type
     */
    protected ?TypeInterface $errType = null;

    /**
     * Set the Ok type.
     */
    public function setOkType(TypeInterface $type): void
    {
        $this->okType = $type;
    }

    /**
     * Set the Err type.
     */
    public function setErrType(TypeInterface $type): void
    {
        $this->errType = $type;
    }

    /**
     * Set both Ok and Err types.
     */
    public function setTypes(TypeInterface $okType, TypeInterface $errType): void
    {
        $this->okType = $okType;
        $this->errType = $errType;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        if (!is_array($value) || count($value) !== 1) {
            throw ScaleEncodeException::invalidType('Result', $value);
        }

        $key = array_key_first($value);
        $data = $value[$key];

        if ($key === 'Ok') {
            if ($this->okType === null) {
                throw new ScaleEncodeException('Result Ok type not set');
            }
            return ScaleBytes::fromBytes([0x00])->concat($this->okType->encode($data));
        }

        if ($key === 'Err') {
            if ($this->errType === null) {
                throw new ScaleEncodeException('Result Err type not set');
            }
            return ScaleBytes::fromBytes([0x01])->concat($this->errType->encode($data));
        }

        throw new ScaleEncodeException(sprintf('Invalid Result key: %s (expected Ok or Err)', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): array
    {
        $flag = $bytes->readByte();

        if ($flag === 0x00) {
            if ($this->okType === null) {
                throw new ScaleDecodeException('Result Ok type not set');
            }
            return ['Ok' => $this->okType->decode($bytes)];
        }

        if ($flag === 0x01) {
            if ($this->errType === null) {
                throw new ScaleDecodeException('Result Err type not set');
            }
            return ['Err' => $this->errType->decode($bytes)];
        }

        throw new ScaleDecodeException(sprintf('Invalid Result flag: %d', $flag));
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if (!is_array($value) || count($value) !== 1) {
            return false;
        }

        $key = array_key_first($value);
        return $key === 'Ok' || $key === 'Err';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'Result';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeString(): string
    {
        $okStr = $this->okType?->getTypeString() ?? '?';
        $errStr = $this->errType?->getTypeString() ?? '?';
        return sprintf('Result<%s, %s>', $okStr, $errStr);
    }
}
