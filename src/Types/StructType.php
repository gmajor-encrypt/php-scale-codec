<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * Struct type implementation.
 * 
 * A collection of named fields with their types.
 * Encoded as: field1_value + field2_value + ... (in field order)
 */
class StructType extends AbstractType
{
    /**
     * @var array<string, TypeInterface> Field name => type mapping
     */
    protected array $fields = [];

    /**
     * @var array<string> Field names in order
     */
    protected array $fieldOrder = [];

    /**
     * Add a field to the struct.
     *
     * @param string $name Field name
     * @param TypeInterface $type Field type
     */
    public function addField(string $name, TypeInterface $type): void
    {
        if (!isset($this->fields[$name])) {
            $this->fieldOrder[] = $name;
        }
        $this->fields[$name] = $type;
    }

    /**
     * Set all fields at once.
     *
     * @param array<string, TypeInterface> $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = [];
        $this->fieldOrder = [];
        foreach ($fields as $name => $type) {
            $this->addField($name, $type);
        }
    }

    /**
     * Get field names in order.
     *
     * @return array<string>
     */
    public function getFieldNames(): array
    {
        return $this->fieldOrder;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        if (!is_array($value)) {
            throw ScaleEncodeException::invalidType('Struct', $value);
        }

        $result = ScaleBytes::empty();

        foreach ($this->fieldOrder as $name) {
            if (!array_key_exists($name, $value)) {
                throw new ScaleEncodeException(sprintf('Missing field "%s" in struct', $name));
            }

            $result = $result->concat($this->fields[$name]->encode($value[$name]));
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): array
    {
        $result = [];

        foreach ($this->fieldOrder as $name) {
            $result[$name] = $this->fields[$name]->decode($bytes);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        // Check all required fields are present and valid
        foreach ($this->fieldOrder as $name) {
            if (!array_key_exists($name, $value)) {
                return false;
            }

            if (!$this->fields[$name]->isValid($value[$name])) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'Struct';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeString(): string
    {
        $parts = [];
        foreach ($this->fieldOrder as $name) {
            $parts[] = sprintf('%s: %s', $name, $this->fields[$name]->getTypeString());
        }
        return '{' . implode(', ', $parts) . '}';
    }
}
