<?php

namespace Jungi\FrameworkExtraBundle\Converter;

/**
 * Uses the type declarations (aka type hints) for the safe type conversion.
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class BuiltinTypeSafeConverter implements ConverterInterface
{
    public function convert($value, $type)
    {
        try {
            switch ($type) {
                case 'array':
                    return $this->convertToArray($value);
                case 'object':
                    return $this->convertToObject($value);
                case 'int':
                    $result = @$this->convertToInt($value);
                    $this->handleNonNumericValueNotice();

                    return $result;
                case 'float':
                    $result = @$this->convertToFloat($value);
                    $this->handleNonNumericValueNotice();

                    return $result;
                case 'bool':
                    return $this->convertToBool($value);
                case 'string':
                    return $this->convertToString($value);
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown type "%s".', $type));
            }
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

    private function convertToObject(object $value): object
    {
        return $value;
    }

    private function convertToArray(array $value): array
    {
        return $value;
    }

    private function handleNonNumericValueNotice()
    {
        $error = error_get_last();
        if ($error && 'A non well formed numeric value encountered' === $error['message']) {
            error_clear_last();

            throw new TypeConversionException($error['message']);
        }
    }
}
