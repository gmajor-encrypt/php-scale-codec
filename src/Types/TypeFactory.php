<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Types;

use Substrate\ScaleCodec\Exception\InvalidTypeException;

/**
 * Factory for creating SCALE type instances.
 * 
 * Parses type strings and creates appropriate type instances.
 */
class TypeFactory
{
    /**
     * @var TypeRegistry The type registry
     */
    private TypeRegistry $registry;

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

        // Check for parameterized type (e.g., Vec<U8>)
        if (str_ends_with($typeString, '>')) {
            return $this->createParameterizedType($typeString);
        }

        // Check for tuple (e.g., (U8, U16))
        if (str_starts_with($typeString, '(') && str_ends_with($typeString, ')')) {
            return $this->createTupleType($typeString);
        }

        // Check for fixed array (e.g., [U8; 32])
        if (str_starts_with($typeString, '[') && str_ends_with($typeString, ']')) {
            return $this->createFixedArrayType($typeString);
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
     * Create a parameterized type.
     *
     * @param string $typeString The type string
     * @return TypeInterface The type instance
     */
    private function createParameterizedType(string $typeString): TypeInterface
    {
        if (!preg_match('/^([^<]+)<(.+)>$/', $typeString, $matches)) {
            throw InvalidTypeException::invalidFormat($typeString, 'Invalid parameterized type format');
        }

        $baseType = strtolower(trim($matches[1]));
        $innerTypeString = $matches[2];

        if (!$this->registry->has($baseType)) {
            throw InvalidTypeException::notRegistered($baseType);
        }

        $type = $this->registry->get($baseType);
        $innerType = $this->create($innerTypeString);
        $type->setInnerType($innerType);
        $type->setTypeString($typeString);

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
        $innerString = substr($typeString, 1, -1);
        $elementTypes = $this->splitTypeString($innerString);

        if (!$this->registry->has('tuple')) {
            throw InvalidTypeException::notRegistered('tuple');
        }

        $type = $this->registry->get('tuple');
        // Tuple implementation would handle the element types internally
        $type->setTypeString($typeString);

        return $type;
    }

    /**
     * Create a fixed array type.
     *
     * @param string $typeString The type string
     * @return TypeInterface The type instance
     */
    private function createFixedArrayType(string $typeString): TypeInterface
    {
        $innerString = substr($typeString, 1, -1);
        $parts = explode(';', $innerString);

        if (count($parts) !== 2) {
            throw InvalidTypeException::invalidFormat($typeString, 'Fixed array must have format [Type; N]');
        }

        $elementType = trim($parts[0]);
        $length = (int)trim($parts[1]);

        if ($length <= 0) {
            throw InvalidTypeException::invalidFormat($typeString, 'Fixed array length must be positive');
        }

        if (!$this->registry->has('fixedarray')) {
            throw InvalidTypeException::notRegistered('fixedarray');
        }

        $type = $this->registry->get('fixedarray');
        $innerType = $this->create($elementType);
        $type->setInnerType($innerType);
        $type->setTypeString($typeString);

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

        // Handle empty tuple as Null
        if ($typeString === '()') {
            return 'null';
        }

        // Handle common aliases
        $aliases = [
            'T::' => '',
            'VecDeque<' => 'Vec<',
            '<T>' => '',
            '<T, I>' => '',
            "&'static[u8]" => 'Bytes',
        ];

        foreach ($aliases as $search => $replace) {
            $typeString = str_replace($search, $replace, $typeString);
        }

        return $typeString;
    }
}
