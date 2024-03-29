<?php

namespace Jungi\FrameworkExtraBundle\Converter;

use Psr\Container\ContainerInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class ConverterManager implements ConverterInterface
{
    private ContainerInterface $converters;

    public function __construct(ContainerInterface $converters)
    {
        $this->converters = $converters;
    }

    public function convert(mixed $value, string $type): mixed
    {
        if ('object' === $type) {
            throw new \InvalidArgumentException('Type "object" is too ambiguous, provide a concrete class type.');
        }

        if ($this->converters->has($type)) {
            /** @var ConverterInterface $converter */
            $converter = $this->converters->get($type);

            return $converter->convert($value, $type);
        }

        // fallback to object converter if available
        if (class_exists($type) && $this->converters->has('object')) {
            /** @var ConverterInterface $converter */
            $converter = $this->converters->get('object');

            return $converter->convert($value, $type);
        }

        throw new \InvalidArgumentException(sprintf('Unsupported type "%s".', $type));
    }
}
