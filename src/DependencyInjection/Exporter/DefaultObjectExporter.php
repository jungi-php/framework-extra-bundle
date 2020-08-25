<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection\Exporter;

use Exception;
use Jungi\FrameworkExtraBundle\DependencyInjection\Exportable;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class DefaultObjectExporter implements ObjectExporterInterface
{
    public function export(object $object): Definition
    {
        $class = get_class($object);

        // when the object implements __set_state is automatically considered as exportable
        if (method_exists($object, '__set_state')) {
            $properties = [];
            $refl = new \ReflectionClass($object);

            do {
                foreach ($refl->getProperties() as $property) {
                    $property->setAccessible(true);
                    $properties[$property->getName()] = $this->exportValue($property->getValue($object));
                }
            } while ($refl = $refl->getParentClass());

            $definition = new Definition($class);
            $definition
                ->setFactory([$definition->getClass(), '__set_state'])
                ->addArgument($properties);

            return $definition;
        }

        // it may be still exportable but do not implement __set_state
        // helpful for classes without state
        if ($object instanceof Exportable) {
            return new Definition($class);
        }

        // use serialize approach as fallback
        try {
            $serialized = serialize($object);
        } catch (Exception $e) {
            throw new InvalidArgumentException(sprintf('Could not serialize object of the type "%s": %s.', $class, $e->getMessage()), null, $e);
        }

        return (new Definition($class))
            ->setFactory('unserialize')
            ->addArgument($serialized);
    }

    private function exportValue($value)
    {
        if (is_array($value)) {
            return array_map(function ($v) { return $this->exportValue($v); }, $value);
        }

        if (is_object($value)) {
            return $this->export($value);
        }

        if (is_resource($value)) {
            throw new InvalidArgumentException('Unable to export resources.');
        }

        return $value;
    }
}
