<?php

namespace Jungi\FrameworkExtraBundle\Converter;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class TypeUtils
{
    /**
     * @param mixed  $value
     * @param string $type
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public static function isValueOfType($value, string $type): bool
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
}
