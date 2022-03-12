<?php

namespace Jungi\FrameworkExtraBundle\Converter;

/**
 * Uses the type declarations (aka type hints) for the safe type conversion.
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class BuiltinTypeSafeConverter implements ConverterInterface
{
    public function convert(mixed $value, string $type): string|int|bool|float
    {
        try {
            return match ($type) {
                'int' => $this->convertToInt($value),
                'float' => $this->convertToFloat($value),
                'bool' => $this->convertToBool($value),
                'string' => $this->convertToString($value),
                default => throw new \InvalidArgumentException(sprintf('Unsupported type "%s".', $type)),
            };
        } catch (\TypeError $e) {
            throw new TypeConversionException($e->getMessage(), 0, $e);
        }
    }

    private function convertToInt(int $value): int
    {
        return $value;
    }

    private function convertToFloat(float $value): float
    {
        return $value;
    }

    private function convertToBool(bool $value): bool
    {
        return $value;
    }

    private function convertToString(string $value): string
    {
        return $value;
    }
}
