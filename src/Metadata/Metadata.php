<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Metadata;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;
use Substrate\ScaleCodec\Types\{TypeRegistry, Compact};

/**
 * Represents parsed Substrate metadata.
 */
class Metadata
{
    /**
     * @var array<int, TypeDefinition> Type definitions indexed by ID
     */
    private array $types = [];

    /**
     * @var array<string, Pallet> Pallets indexed by name
     */
    private array $pallets = [];

    /**
     * @var array<string, Pallet> Pallets indexed by index
     */
    private array $palletsByIndex = [];

    /**
     * @var array|null Cached type map for quick lookup
     */
    private ?array $typeCache = null;

    /**
     * @param MetadataVersion $version Metadata version
     * @param array $apis Runtime APIs (v15+)
     * @param array $extrinsic Extrinsic metadata
     * @param array $outerEvent Outer event types
     */
    public function __construct(
        public readonly MetadataVersion $version,
        public readonly array $apis = [],
        public readonly array $extrinsic = [],
        public readonly array $outerEvent = [],
    ) {}

    /**
     * Add a type definition.
     */
    public function addType(TypeDefinition $type): void
    {
        $this->types[$type->id] = $type;
        $this->typeCache = null; // Invalidate cache
    }

    /**
     * Get a type definition by ID.
     */
    public function getType(int $id): ?TypeDefinition
    {
        return $this->types[$id] ?? null;
    }

    /**
     * Get all type definitions.
     *
     * @return array<int, TypeDefinition>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Add a pallet.
     */
    public function addPallet(Pallet $pallet): void
    {
        $this->pallets[$pallet->name] = $pallet;
        $this->palletsByIndex[$pallet->index] = $pallet;
    }

    /**
     * Get a pallet by name.
     */
    public function getPallet(string $name): ?Pallet
    {
        return $this->pallets[$name] ?? null;
    }

    /**
     * Get a pallet by index.
     */
    public function getPalletByIndex(int $index): ?Pallet
    {
        return $this->palletsByIndex[$index] ?? null;
    }

    /**
     * Get all pallets.
     *
     * @return array<string, Pallet>
     */
    public function getPallets(): array
    {
        return $this->pallets;
    }

    /**
     * Get type ID by name/path.
     */
    public function getTypeIdByName(string $name): ?int
    {
        // Build cache if needed
        if ($this->typeCache === null) {
            $this->typeCache = [];
            foreach ($this->types as $type) {
                if (!empty($type->path)) {
                    $path = implode('::', $type->path);
                    $this->typeCache[$path] = $type->id;
                    $this->typeCache[end($type->path)] = $type->id; // Also index by last segment
                }
            }
        }

        return $this->typeCache[$name] ?? null;
    }

    /**
     * Get extrinsic version.
     */
    public function getExtrinsicVersion(): int
    {
        return $this->extrinsic['version'] ?? 4;
    }

    /**
     * Get extrinsic address type.
     */
    public function getExtrinsicAddressType(): ?int
    {
        return $this->extrinsic['addressType'] ?? null;
    }

    /**
     * Get extrinsic call type.
     */
    public function getExtrinsicCallType(): ?int
    {
        return $this->extrinsic['callType'] ?? null;
    }

    /**
     * Get extrinsic signature type.
     */
    public function getExtrinsicSignatureType(): ?int
    {
        return $this->extrinsic['signatureType'] ?? null;
    }

    /**
     * Get extrinsic extra type.
     */
    public function getExtrinsicExtraType(): ?int
    {
        return $this->extrinsic['extraType'] ?? null;
    }

    /**
     * Register types from metadata into the registry.
     */
    public function registerTypes(TypeRegistry $registry): void
    {
        $typeDefinitions = [];
        
        foreach ($this->types as $type) {
            $name = !empty($type->path) ? implode('::', $type->path) : "Type{$type->id}";
            $typeDefinitions[$name] = $this->convertToRegistryFormat($type);
        }

        $registry->registerFromMetadata($typeDefinitions);
    }

    /**
     * Convert TypeDefinition to registry format.
     */
    private function convertToRegistryFormat(TypeDefinition $type): array
    {
        $result = ['id' => $type->id];

        if ($type->isComposite()) {
            $result['type'] = 'struct';
            $result['fields'] = [];
            foreach ($type->getFields() as $field) {
                $result['fields'][] = [
                    'name' => $field['name'] ?? '',
                    'type' => $field['type'] ?? 0,
                ];
            }
        } elseif ($type->isVariant()) {
            $result['type'] = 'enum';
            $result['variants'] = [];
            foreach ($type->getVariants() as $variant) {
                $result['variants'][] = [
                    'name' => $variant['name'] ?? '',
                    'index' => $variant['index'] ?? 0,
                    'fields' => $variant['fields'] ?? null,
                ];
            }
        } elseif ($type->isSequence()) {
            $result['type'] = 'sequence';
            $result['elementType'] = $type->getElementType();
        } elseif ($type->isArray()) {
            $arrInfo = $type->getArrayInfo();
            $result['type'] = 'array';
            $result['elementType'] = $arrInfo['type'];
            $result['length'] = $arrInfo['len'];
        } elseif ($type->isTuple()) {
            $result['type'] = 'tuple';
            $result['types'] = $type->getTupleTypes();
        }

        return $result;
    }
}
