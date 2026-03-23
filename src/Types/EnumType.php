<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleEncodeException;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;

/**
 * Enum type implementation.
 * 
 * Represents a variant type with named variants.
 * Encoded as: variant_index (U8) + variant_value (if variant has data)
 */
class EnumType extends AbstractType
{
    /**
     * @var array<string, array{index: int, fields?: array<string, TypeInterface>|TypeInterface|null}> Variant definitions
     */
    protected array $variants = [];

    /**
     * @var array<int, string> Index to name mapping
     */
    protected array $indexToName = [];

    /**
     * Add a variant.
     *
     * @param string $name Variant name
     * @param int $index Variant index
     * @param array<string, TypeInterface>|TypeInterface|null $fields Field types or single type
     */
    public function addVariant(string $name, int $index, array|TypeInterface|null $fields = null): void
    {
        $this->variants[$name] = [
            'index' => $index,
            'fields' => $fields,
        ];
        $this->indexToName[$index] = $name;
    }

    /**
     * Set all variants.
     *
     * @param array<string, array{index: int, fields?: array<string, TypeInterface>|TypeInterface|null}> $variants
     */
    public function setVariants(array $variants): void
    {
        $this->variants = [];
        $this->indexToName = [];
        foreach ($variants as $name => $variant) {
            $this->addVariant($name, $variant['index'], $variant['fields'] ?? null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function encode(mixed $value): ScaleBytes
    {
        if (!is_array($value)) {
            throw ScaleEncodeException::invalidType('Enum', $value);
        }

        // Value should be ['variant_name' => data] or just 'variant_name' for unit variants
        if (count($value) !== 1) {
            throw new ScaleEncodeException('Enum value must have exactly one variant');
        }

        $variantName = array_key_first($value);
        
        if (!isset($this->variants[$variantName])) {
            throw new ScaleEncodeException(sprintf('Unknown enum variant: %s', $variantName));
        }

        $variant = $this->variants[$variantName];
        
        // Encode variant index as U8
        $result = ScaleBytes::fromBytes([$variant['index']]);

        // Encode variant data if present
        $data = $value[$variantName];
        
        if ($variant['fields'] !== null && $data !== null) {
            if ($variant['fields'] instanceof TypeInterface) {
                // Single unnamed field
                $result = $result->concat($variant['fields']->encode($data));
            } elseif (is_array($variant['fields'])) {
                // Named fields - expect array with field values
                $fieldResult = ScaleBytes::empty();
                foreach ($variant['fields'] as $fieldName => $fieldType) {
                    if (!is_array($data) || !array_key_exists($fieldName, $data)) {
                        throw new ScaleEncodeException(
                            sprintf('Missing field "%s" in enum variant "%s"', $fieldName, $variantName)
                        );
                    }
                    $fieldResult = $fieldResult->concat($fieldType->encode($data[$fieldName]));
                }
                $result = $result->concat($fieldResult);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function decode(ScaleBytes $bytes): array
    {
        $index = $bytes->readByte();

        if (!isset($this->indexToName[$index])) {
            throw ScaleDecodeException::invalidEnumVariant($index, array_keys($this->indexToName));
        }

        $variantName = $this->indexToName[$index];
        $variant = $this->variants[$variantName];

        if ($variant['fields'] === null) {
            // Unit variant
            return [$variantName => null];
        }

        if ($variant['fields'] instanceof TypeInterface) {
            // Single unnamed field
            return [$variantName => $variant['fields']->decode($bytes)];
        }

        // Named fields
        $data = [];
        foreach ($variant['fields'] as $fieldName => $fieldType) {
            $data[$fieldName] = $fieldType->decode($bytes);
        }

        return [$variantName => $data];
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(mixed $value): bool
    {
        if (!is_array($value) || count($value) !== 1) {
            return false;
        }

        $variantName = array_key_first($value);
        
        if (!isset($this->variants[$variantName])) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeName(): string
    {
        return 'Enum';
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeString(): string
    {
        $parts = [];
        foreach ($this->variants as $name => $variant) {
            if ($variant['fields'] === null) {
                $parts[] = $name;
            } elseif ($variant['fields'] instanceof TypeInterface) {
                $parts[] = sprintf('%s(%s)', $name, $variant['fields']->getTypeString());
            } else {
                $fieldParts = [];
                foreach ($variant['fields'] as $fieldName => $fieldType) {
                    $fieldParts[] = sprintf('%s: %s', $fieldName, $fieldType->getTypeString());
                }
                $parts[] = sprintf('%s{%s}', $name, implode(', ', $fieldParts));
            }
        }
        return implode(' | ', $parts);
    }
}
