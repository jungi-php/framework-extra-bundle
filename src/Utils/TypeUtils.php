<?php

namespace Jungi\FrameworkExtraBundle\Utils;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
final class TypeUtils
{
    /**
     * @throws \InvalidArgumentException
     */
    public static function isValueOfType(mixed $value, string $type): bool
    {
        if (class_exists($type) || interface_exists($type)) {
            return $value instanceof $type;
        }

        $fnName = 'is_'.$type;

        if (!function_exists($fnName)) {
            throw new \InvalidArgumentException(sprintf('Undefined type "%s".', $type));
        }

        return $fnName($value);
    }

    public static function isCollection(string $type): bool
    {
        return str_ends_with($type, '[]');
    }

    public static function getCollectionBaseElementType(string $type): string
    {
        if (false === $elementType = strstr($type, '[]', true)) {
            throw new \InvalidArgumentException(sprintf('Expected a collection type, got "%s".', $type));
        }

        return $elementType;
    }
}
