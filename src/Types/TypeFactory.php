<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Exception\InvalidTypeException;

/**
 * Factory for creating SCALE type instances.
 * 
 * Features:
 * - Parses type strings and creates appropriate type instances
 * - Supports parameterized types (Vec<U8>, Option<Bool>)
 * - Supports tuples ((U8, U16))
 * - Supports fixed arrays ([U8; 32])
 * - Type caching for performance
 */
class TypeFactory
{
    /**
     * @var TypeRegistry The type registry
     */
    private TypeRegistry $registry;

    /**
     * @var array<string, TypeInterface> Resolved type cache
     */
    private array $resolvedTypes = [];

    /**
     * Create a new factory.
     *
     * @param TypeRegistry $registry The type registry
     */
    public function __construct(TypeRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Create a type instance from a type string.
     *
     * @param string $typeString The type string (e.g., "Vec<U8>", "Option<Bool>")
     * @return TypeInterface The type instance
     * @throws InvalidTypeException If the type string is invalid
     */
    public function create(string $typeString): TypeInterface
    {
        $typeString = $this->normalizeTypeString($typeString);

        // Check cache
        if (isset($this->resolvedTypes[$typeString])) {
            return clone $this->resolvedTypes[$typeString];
        }

        // Check for parameterized type (e.g., Vec<U8>)
        if (preg_match('/^(\w+)<(.+)>$/i', $typeString, $matches)) {
            $type = $this->createParameterizedType($matches[1], $matches[2]);
            $this->resolvedTypes[$typeString] = $type;
            return $type;
        }

        // Check for tuple (e.g., (U8, U16))
        if (str_starts_with($typeString, '(') && str_ends_with($typeString, ')')) {
            $type = $this->createTupleType($typeString);
            $this->resolvedTypes[$typeString] = $type;
            return $type;
        }

        // Check for fixed array (e.g., [U8; 32])
        if (preg_match('/^\[(.+);\s*(\d+)\]$/', $typeString, $matches)) {
            $type = $this->createFixedArrayType($matches[1], (int)$matches[2]);
            $this->resolvedTypes[$typeString] = $type;
            return $type;
        }

        // Simple type lookup
        if ($this->registry->has($typeString)) {
            return $this->registry->get($typeString);
        }

        throw InvalidTypeException::notRegistered($typeString);
    }

    /**
     * Check if a type string represents a valid type.
     *
     * @param string $typeString The type string
     * @return bool True if the type is valid
     */
    public function isValidTypeString(string $typeString): bool
    {
        try {
            $this->create($typeString);
            return true;
        } catch (InvalidTypeException) {
            return false;
        }
    }

    /**
     * Clear the resolved type cache.
     */
    public function clearCache(): void
    {
        $this->resolvedTypes = [];
    }

    /**
     * Create a parameterized type.
     *
     * @param string $baseType The base type name
     * @param string $innerTypesStr The inner type string(s)
     * @return TypeInterface The type instance
     */
    private function createParameterizedType(string $baseType, string $innerTypesStr): TypeInterface
    {
        $baseType = strtolower(trim($baseType));

        // Split inner types by comma, respecting nested brackets
        $innerTypes = $this->splitTypeString($innerTypesStr);

        // Get the base type from registry
        if (!$this->registry->has($baseType)) {
            throw InvalidTypeException::notRegistered($baseType);
        }

        $type = $this->registry->get($baseType);

        // Handle different base types
        switch ($baseType) {
            case 'vec':
            case 'option':
                if (count($innerTypes) !== 1) {
                    throw new InvalidTypeException(sprintf('%s requires exactly one type parameter', $baseType));
                }
                $type->setInnerType($this->create($innerTypes[0]));
                break;

            case 'result':
                if (count($innerTypes) !== 2) {
                    throw new InvalidTypeException('Result requires exactly two type parameters');
                }
                $type->setOkType($this->create($innerTypes[0]));
                $type->setErrType($this->create($innerTypes[1]));
                break;

            case 'compact':
                if (count($innerTypes) !== 1) {
                    throw new InvalidTypeException('Compact requires exactly one type parameter');
                }
                $type->setInnerTypeName($innerTypes[0]);
                break;

            default:
                // Generic parameterized type - try to set inner type
                if (count($innerTypes) >= 1) {
                    $type->setInnerType($this->create($innerTypes[0]));
                }
        }

        return $type;
    }

    /**
     * Create a tuple type.
     *
     * @param string $typeString The type string
     * @return TypeInterface The type instance
     */
    private function createTupleType(string $typeString): TypeInterface
    {
        // Handle empty tuple as Null
        if ($typeString === '()') {
            return $this->registry->get('null');
        }

        $innerString = substr($typeString, 1, -1);
        $elementTypes = $this->splitTypeString($innerString);

        if (!$this->registry->has('tuple')) {
            throw InvalidTypeException::notRegistered('tuple');
        }

        $type = $this->registry->get('tuple');

        foreach ($elementTypes as $elementType) {
            $type->addElementType($this->create($elementType));
        }

        return $type;
    }

    /**
     * Create a fixed array type.
     *
     * @param string $elementTypeStr The element type string
     * @param int $length The array length
     * @return TypeInterface The type instance
     */
    private function createFixedArrayType(string $elementTypeStr, int $length): TypeInterface
    {
        if ($length <= 0) {
            throw new InvalidTypeException('Fixed array length must be positive');
        }

        if (!$this->registry->has('fixedarray')) {
            throw InvalidTypeException::notRegistered('fixedarray');
        }

        $type = $this->registry->get('fixedarray');
        $type->setElementType($this->create($elementTypeStr));
        $type->setLength($length);

        return $type;
    }

    /**
     * Split a type string by comma, respecting nested angle brackets.
     *
     * @param string $typeString The type string
     * @return array<string> The split type strings
     */
    private function splitTypeString(string $typeString): array
    {
        $result = [];
        $current = '';
        $depth = 0;

        for ($i = 0; $i < strlen($typeString); $i++) {
            $char = $typeString[$i];

            if ($char === '<' || $char === '(' || $char === '[') {
                $depth++;
                $current .= $char;
            } elseif ($char === '>' || $char === ')' || $char === ']') {
                $depth--;
                $current .= $char;
            } elseif ($char === ',' && $depth === 0) {
                $result[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (trim($current) !== '') {
            $result[] = trim($current);
        }

        return $result;
    }

    /**
     * Normalize a type string.
     *
     * @param string $typeString The type string
     * @return string The normalized type string
     */
    private function normalizeTypeString(string $typeString): string
    {
        $typeString = trim($typeString);

        // Remove whitespace around brackets
        $typeString = preg_replace('/\s*([<>,()\[;\]])\s*/i', '$1', $typeString);

        // Handle common aliases
        $aliases = [
            'T::' => '',
            'VecDeque<' => 'Vec<',
            "&'static[u8]" => 'Bytes',
            "&'static str" => 'String',
        ];

        foreach ($aliases as $search => $replace) {
            $typeString = str_ireplace($search, $replace, $typeString);
        }

        return $typeString;
    }
}
