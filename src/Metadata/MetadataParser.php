<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Metadata;

use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Exception\ScaleDecodeException;
use Substrate\ScaleCodec\Types\TypeRegistry;

/**
 * Parser for Substrate SCALE-encoded metadata.
 * 
 * Supports versions 12, 13, 14, and 15.
 */
class MetadataParser
{
    private TypeRegistry $registry;

    /**
     * @var array Cached parsed metadata
     */
    private static array $cache = [];

    /**
     * Create a new metadata parser.
     */
    public function __construct(?TypeRegistry $registry = null)
    {
        $this->registry = $registry ?? new TypeRegistry();
    }

    /**
     * Parse metadata from hex string.
     *
     * @param string $hex Hex string (with or without 0x prefix)
     * @param bool $useCache Whether to use cached result
     * @return Metadata Parsed metadata
     * @throws ScaleDecodeException If parsing fails
     */
    public function parse(string $hex, bool $useCache = true): Metadata
    {
        // Normalize hex
        if (!str_starts_with($hex, '0x')) {
            $hex = '0x' . $hex;
        }

        // Check cache
        $cacheKey = md5($hex);
        if ($useCache && isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $bytes = ScaleBytes::fromHex($hex);
        $metadata = $this->parseBytes($bytes);

        // Cache result
        if ($useCache) {
            self::$cache[$cacheKey] = $metadata;
        }

        return $metadata;
    }

    /**
     * Parse metadata from bytes.
     */
    private function parseBytes(ScaleBytes $bytes): Metadata
    {
        // Read magic number
        $magic = $this->readU32($bytes);
        if ($magic !== 0x6174656D) { // "meta" in little-endian
            throw new ScaleDecodeException('Invalid metadata magic number');
        }

        // Read version
        $version = $bytes->readByte();
        $metadataVersion = MetadataVersion::fromInt($version);

        if ($metadataVersion === null) {
            throw new ScaleDecodeException("Unsupported metadata version: $version");
        }

        // Parse based on version
        return match ($metadataVersion) {
            MetadataVersion::V12, MetadataVersion::V13 => $this->parseV12V13($bytes, $metadataVersion),
            MetadataVersion::V14 => $this->parseV14($bytes, $metadataVersion),
            MetadataVersion::V15 => $this->parseV15($bytes, $metadataVersion),
        };
    }

    /**
     * Parse v12/v13 metadata.
     */
    private function parseV12V13(ScaleBytes $bytes, MetadataVersion $version): Metadata
    {
        $metadata = new Metadata($version);

        // Parse modules
        $moduleCount = $this->readCompact($bytes);

        for ($i = 0; $i < $moduleCount; $i++) {
            $pallet = $this->parseModuleV12V13($bytes, $i);
            $metadata->addPallet($pallet);
        }

        return $metadata;
    }

    /**
     * Parse a module (pallet) for v12/v13.
     */
    private function parseModuleV12V13(ScaleBytes $bytes, int $index): Pallet
    {
        $name = $this->readString($bytes);

        // Parse storage if present
        $storage = [];
        $hasStorage = $bytes->readByte();
        if ($hasStorage) {
            $storagePrefix = $this->readString($bytes);
            $storageCount = $this->readCompact($bytes);
            for ($i = 0; $i < $storageCount; $i++) {
                $storage[] = $this->parseStorageEntry($bytes);
            }
        }

        // Parse calls if present
        $calls = [];
        $hasCalls = $bytes->readByte();
        if ($hasCalls) {
            $callCount = $this->readCompact($bytes);
            for ($i = 0; $i < $callCount; $i++) {
                $calls[] = $this->parseFunction($bytes);
            }
        }

        // Parse events if present
        $events = [];
        $hasEvents = $bytes->readByte();
        if ($hasEvents) {
            $eventCount = $this->readCompact($bytes);
            for ($i = 0; $i < $eventCount; $i++) {
                $events[] = $this->parseFunction($bytes);
            }
        }

        // Parse constants
        $constantCount = $this->readCompact($bytes);
        $constants = [];
        for ($i = 0; $i < $constantCount; $i++) {
            $constants[] = $this->parseConstant($bytes);
        }

        // Parse errors if present
        $errors = [];
        $hasErrors = $bytes->readByte();
        if ($hasErrors) {
            $errorCount = $this->readCompact($bytes);
            for ($i = 0; $i < $errorCount; $i++) {
                $errors[] = $this->parseFunction($bytes);
            }
        }

        return new Pallet(
            name: $name,
            index: $index,
            storage: $storage,
            calls: $calls,
            events: $events,
            errors: $errors,
            constants: $constants,
        );
    }

    /**
     * Parse v14 metadata.
     */
    private function parseV14(ScaleBytes $bytes, MetadataVersion $version): Metadata
    {
        $metadata = new Metadata($version);

        // Parse types
        $typeCount = $this->readCompact($bytes);
        for ($i = 0; $i < $typeCount; $i++) {
            $type = $this->parseType($bytes, $i);
            $metadata->addType($type);
        }

        // Parse pallets
        $palletCount = $this->readCompact($bytes);
        for ($i = 0; $i < $palletCount; $i++) {
            $pallet = $this->parsePalletV14($bytes, $i, $metadata);
            $metadata->addPallet($pallet);
        }

        // Parse extrinsic
        $extrinsic = $this->parseExtrinsic($bytes);
        $metadata = new Metadata(
            version: $version,
            extrinsic: $extrinsic,
        );

        // Re-add types and pallets
        foreach ($this->types ?? [] as $type) {
            $metadata->addType($type);
        }

        return $metadata;
    }

    /**
     * Parse v15 metadata.
     */
    private function parseV15(ScaleBytes $bytes, MetadataVersion $version): Metadata
    {
        // Parse types first
        $types = [];
        $typeCount = $this->readCompact($bytes);
        for ($i = 0; $i < $typeCount; $i++) {
            $types[$i] = $this->parseType($bytes, $i);
        }

        // Parse pallets
        $pallets = [];
        $palletCount = $this->readCompact($bytes);
        for ($i = 0; $i < $palletCount; $i++) {
            $pallets[$i] = $this->parsePalletV14($bytes, $i);
        }

        // Parse extrinsic
        $extrinsic = $this->parseExtrinsic($bytes);

        // Parse runtime APIs (new in v15)
        $apiCount = $this->readCompact($bytes);
        $apis = [];
        for ($i = 0; $i < $apiCount; $i++) {
            $apis[] = $this->parseRuntimeApi($bytes);
        }

        // Parse outer event types (optional in v15)
        $outerEvent = [];
        if ($bytes->hasRemaining()) {
            try {
                $outerEvent = $this->parseOuterEvent($bytes);
            } catch (\Exception) {
                // Ignore if not present
            }
        }

        $metadata = new Metadata(
            version: $version,
            apis: $apis,
            extrinsic: $extrinsic,
            outerEvent: $outerEvent,
        );

        foreach ($types as $type) {
            $metadata->addType($type);
        }

        foreach ($pallets as $pallet) {
            $metadata->addPallet($pallet);
        }

        return $metadata;
    }

    /**
     * Parse a type definition.
     */
    private function parseType(ScaleBytes $bytes, int $id): TypeDefinition
    {
        $path = $this->parsePath($bytes);

        // Parse type parameters
        $paramCount = $this->readCompact($bytes);
        $params = [];
        for ($i = 0; $i < $paramCount; $i++) {
            $params[] = $this->parseTypeParameter($bytes);
        }

        // Parse type definition
        $def = $this->parseTypeDef($bytes);

        // Parse docs
        $docs = $this->parseDocs($bytes);

        return new TypeDefinition(
            id: $id,
            path: implode('::', $path),
            params: $params,
            def: $def,
            docs: $docs,
        );
    }

    /**
     * Parse type path.
     */
    private function parsePath(ScaleBytes $bytes): array
    {
        $count = $this->readCompact($bytes);
        $path = [];
        for ($i = 0; $i < $count; $i++) {
            $path[] = $this->readString($bytes);
        }
        return $path;
    }

    /**
     * Parse type parameter.
     */
    private function parseTypeParameter(ScaleBytes $bytes): array
    {
        $name = $this->readString($bytes);
        $hasType = $bytes->readByte();

        return [
            'name' => $name,
            'type' => $hasType ? $this->readCompact($bytes) : null,
        ];
    }

    /**
     * Parse type definition variant.
     */
    private function parseTypeDef(ScaleBytes $bytes): array
    {
        $kind = $bytes->readByte();

        return match ($kind) {
            0 => ['composite' => ['fields' => $this->parseFields($bytes)]],
            1 => ['variant' => $this->parseVariantDef($bytes)],
            2 => ['sequence' => ['type' => $this->readCompact($bytes)]],
            3 => ['array' => ['type' => $this->readCompact($bytes), 'len' => $this->readCompact($bytes)]],
            4 => ['tuple' => $this->parseTuple($bytes)],
            5 => ['primitive' => $this->parsePrimitive($bytes)],
            6 => ['compact' => ['type' => $this->readCompact($bytes)]],
            7 => ['bitsequence' => true],
            default => throw new ScaleDecodeException("Unknown type def kind: $kind"),
        };
    }

    /**
     * Parse variant definition.
     */
    private function parseVariantDef(ScaleBytes $bytes): array
    {
        $variantCount = $this->readCompact($bytes);
        $variants = [];

        for ($i = 0; $i < $variantCount; $i++) {
            $variants[] = [
                'name' => $this->readString($bytes),
                'index' => $bytes->readByte(),
                'fields' => $this->parseFields($bytes),
                'docs' => $this->parseDocs($bytes),
            ];
        }

        return ['variants' => $variants];
    }

    /**
     * Parse fields.
     */
    private function parseFields(ScaleBytes $bytes): array
    {
        $count = $this->readCompact($bytes);
        $fields = [];

        for ($i = 0; $i < $count; $i++) {
            $field = [
                'name' => null,
                'type' => $this->readCompact($bytes),
                'typeName' => null,
            ];

            $hasName = $bytes->readByte();
            if ($hasName) {
                $field['name'] = $this->readString($bytes);
            }

            $hasTypeName = $bytes->readByte();
            if ($hasTypeName) {
                $field['typeName'] = $this->readString($bytes);
            }

            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Parse tuple type.
     */
    private function parseTuple(ScaleBytes $bytes): array
    {
        $count = $this->readCompact($bytes);
        $types = [];
        for ($i = 0; $i < $count; $i++) {
            $types[] = $this->readCompact($bytes);
        }
        return $types;
    }

    /**
     * Parse primitive type.
     */
    private function parsePrimitive(ScaleBytes $bytes): string
    {
        $kind = $bytes->readByte();

        return match ($kind) {
            0 => 'bool',
            1 => 'char',
            2 => 'str',
            3 => 'U8',
            4 => 'U16',
            5 => 'U32',
            6 => 'U64',
            7 => 'U128',
            8 => 'I8',
            9 => 'I16',
            10 => 'I32',
            11 => 'I64',
            12 => 'I128',
            default => throw new ScaleDecodeException("Unknown primitive kind: $kind"),
        };
    }

    /**
     * Parse documentation.
     */
    private function parseDocs(ScaleBytes $bytes): array
    {
        $count = $this->readCompact($bytes);
        $docs = [];
        for ($i = 0; $i < $count; $i++) {
            $docs[] = $this->readString($bytes);
        }
        return $docs;
    }

    /**
     * Parse pallet v14+.
     */
    private function parsePalletV14(ScaleBytes $bytes, int $index, ?Metadata $metadata = null): Pallet
    {
        $name = $this->readString($bytes);

        // Parse storage
        $storage = [];
        $hasStorage = $bytes->readByte();
        if ($hasStorage) {
            $storagePrefix = $this->readString($bytes);
            $storageCount = $this->readCompact($bytes);
            for ($i = 0; $i < $storageCount; $i++) {
                $storage[] = $this->parseStorageEntry($bytes);
            }
        }

        // Parse calls
        $calls = [];
        $hasCalls = $bytes->readByte();
        if ($hasCalls) {
            $callType = $this->readCompact($bytes);
            $calls = ['type' => $callType];
        }

        // Parse events
        $events = [];
        $hasEvents = $bytes->readByte();
        if ($hasEvents) {
            $eventType = $this->readCompact($bytes);
            $events = ['type' => $eventType];
        }

        // Parse constants
        $constantCount = $this->readCompact($bytes);
        $constants = [];
        for ($i = 0; $i < $constantCount; $i++) {
            $constants[] = $this->parseConstant($bytes);
        }

        // Parse errors
        $errors = [];
        $hasErrors = $bytes->readByte();
        if ($hasErrors) {
            $errorType = $this->readCompact($bytes);
            $errors = ['type' => $errorType];
        }

        // Parse index (v14+)
        $palletIndex = $bytes->readByte();

        return new Pallet(
            name: $name,
            index: $palletIndex,
            storage: $storage,
            calls: $calls,
            events: $events,
            errors: $errors,
            constants: $constants,
        );
    }

    /**
     * Parse storage entry.
     */
    private function parseStorageEntry(ScaleBytes $bytes): array
    {
        $name = $this->readString($bytes);
        $modifier = $bytes->readByte();
        $type = $this->parseStorageEntryType($bytes);
        $fallback = $this->readBytes($bytes);
        $docs = $this->parseDocs($bytes);

        return [
            'name' => $name,
            'modifier' => $modifier,
            'type' => $type,
            'fallback' => $fallback,
            'docs' => $docs,
        ];
    }

    /**
     * Parse storage entry type.
     */
    private function parseStorageEntryType(ScaleBytes $bytes): array
    {
        $kind = $bytes->readByte();

        return match ($kind) {
            0 => ['plain' => $this->readCompact($bytes)],
            1 => [
                'map' => [
                    'hashers' => $this->parseHashers($bytes),
                    'key' => $this->readCompact($bytes),
                    'value' => $this->readCompact($bytes),
                ],
            ],
            default => throw new ScaleDecodeException("Unknown storage entry type: $kind"),
        };
    }

    /**
     * Parse hashers.
     */
    private function parseHashers(ScaleBytes $bytes): array
    {
        $count = $this->readCompact($bytes);
        $hashers = [];
        for ($i = 0; $i < $count; $i++) {
            $hashers[] = $this->parseHasher($bytes);
        }
        return $hashers;
    }

    /**
     * Parse hasher.
     */
    private function parseHasher(ScaleBytes $bytes): string
    {
        $kind = $bytes->readByte();

        return match ($kind) {
            0 => 'Blake2_128',
            1 => 'Blake2_256',
            2 => 'Blake2_128Concat',
            3 => 'Twox128',
            4 => 'Twox256',
            5 => 'Twox64Concat',
            6 => 'Identity',
            default => "Hasher_$kind",
        };
    }

    /**
     * Parse function (call/event/error).
     */
    private function parseFunction(ScaleBytes $bytes): array
    {
        $name = $this->readString($bytes);

        // Parse arguments
        $argCount = $this->readCompact($bytes);
        $args = [];
        for ($i = 0; $i < $argCount; $i++) {
            $args[] = [
                'name' => $this->readString($bytes),
                'type' => $this->readString($bytes),
            ];
        }

        $docs = $this->parseDocs($bytes);

        return [
            'name' => $name,
            'args' => $args,
            'docs' => $docs,
        ];
    }

    /**
     * Parse constant.
     */
    private function parseConstant(ScaleBytes $bytes): array
    {
        return [
            'name' => $this->readString($bytes),
            'type' => $this->readCompact($bytes),
            'value' => $this->readBytes($bytes),
            'docs' => $this->parseDocs($bytes),
        ];
    }

    /**
     * Parse extrinsic metadata.
     */
    private function parseExtrinsic(ScaleBytes $bytes): array
    {
        return [
            'version' => $bytes->readByte(),
            'addressType' => $this->readCompact($bytes),
            'callType' => $this->readCompact($bytes),
            'signatureType' => $this->readCompact($bytes),
            'extraType' => $this->readCompact($bytes),
        ];
    }

    /**
     * Parse runtime API.
     */
    private function parseRuntimeApi(ScaleBytes $bytes): array
    {
        $name = $this->readString($bytes);
        $methodCount = $this->readCompact($bytes);
        $methods = [];

        for ($i = 0; $i < $methodCount; $i++) {
            $methods[] = [
                'name' => $this->readString($bytes),
                'inputs' => $this->readCompact($bytes),
                'output' => $this->readCompact($bytes),
                'docs' => $this->parseDocs($bytes),
            ];
        }

        return [
            'name' => $name,
            'methods' => $methods,
        ];
    }

    /**
     * Parse outer event types.
     */
    private function parseOuterEvent(ScaleBytes $bytes): array
    {
        $count = $this->readCompact($bytes);
        $events = [];

        for ($i = 0; $i < $count; $i++) {
            $events[] = [
                'name' => $this->readString($bytes),
                'type' => $this->readCompact($bytes),
            ];
        }

        return $events;
    }

    /**
     * Read a compact integer.
     */
    private function readCompact(ScaleBytes $bytes): int
    {
        $first = $bytes->readByte();
        $mode = $first & 0x03;

        return match ($mode) {
            0 => $first >> 2,
            1 => ($first >> 2) | ($bytes->readByte() << 6),
            2 => ($first >> 2) | ($bytes->readByte() << 6) | ($bytes->readByte() << 14) | ($bytes->readByte() << 22),
            3 => throw new ScaleDecodeException('Large compact not supported for type IDs'),
        };
    }

    /**
     * Read a string.
     */
    private function readString(ScaleBytes $bytes): string
    {
        $length = $this->readCompact($bytes);
        $rawBytes = $bytes->readBytes($length);
        return pack('C*', ...$rawBytes);
    }

    /**
     * Read bytes (length-prefixed).
     */
    private function readBytes(ScaleBytes $bytes): array
    {
        $length = $this->readCompact($bytes);
        return $bytes->readBytes($length);
    }

    /**
     * Read U32.
     */
    private function readU32(ScaleBytes $bytes): int
    {
        $b = $bytes->readBytes(4);
        return $b[0] | ($b[1] << 8) | ($b[2] << 16) | ($b[3] << 24);
    }

    /**
     * Clear the metadata cache.
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
