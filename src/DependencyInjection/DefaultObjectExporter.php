<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class DefaultObjectExporter implements ObjectExporterInterface
{
    public function export(object $object): Definition
    {
        // use serialize approach as fallback
        if (!$object instanceof ExportableObject) {
            return (new Definition(get_class($object)))
                ->setFactory('unserialize')
                ->addArgument(serialize($object));
        }

        $definition = new Definition(get_class($object));

        if (!$object instanceof StatefulObject) {
            return $definition;
        }

        $properties = array();
        $refl = new \ReflectionClass($object);

        do {
            foreach ($refl->getProperties() as $property) {
                $property->setAccessible(true);
                $properties[$property->getName()] = $this->exportValue($property->getValue($object));
            }
        } while ($refl = $refl->getParentClass());

        $definition
            ->setFactory([$definition->getClass(), 'fromState'])
            ->addArgument($properties);

        return $definition;
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
            throw new InvalidArgumentException('Unable to dump an object that contains resources.');
        }

        return $value;
    }
}
