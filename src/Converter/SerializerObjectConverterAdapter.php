<?php

namespace Jungi\FrameworkExtraBundle\Converter;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class SerializerObjectConverterAdapter implements ConverterInterface
{
    private DenormalizerInterface $denormalizer;
    private array $context;

    public function __construct(DenormalizerInterface $denormalizer, array $context = [])
    {
        $this->denormalizer = $denormalizer;
        $this->context = $context;
    }

    public function convert(mixed $value, string $type): mixed
    {
        if (!class_exists($type)) {
            throw new \InvalidArgumentException('Conversion to class types is only supported.');
        }

        try {
            return $this->denormalizer->denormalize($value, $type, null, $this->context);
        } catch (NotNormalizableValueException | \TypeError $e) {
            throw new TypeConversionException($e->getMessage(), 0, $e);
        }
    }
}
