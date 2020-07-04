<?php

namespace Jungi\FrameworkExtraBundle\Tests\DependencyInjection\Exporter;

use Jungi\FrameworkExtraBundle\Annotation\AnnotationInterface;
use Jungi\FrameworkExtraBundle\DependencyInjection\Exporter\DefaultObjectExporter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Jungi\FrameworkExtraBundle\DependencyInjection\Exportable;

class DefaultObjectExporterTest extends TestCase
{
    /** @test */
    public function exportSerializableObject(): void
    {
        $object = new SerializableObject('serializable');
        $definition = (new DefaultObjectExporter())->export($object);

        $this->assertEquals(SerializableObject::class, $definition->getClass());
        $this->assertEquals('unserialize', $definition->getFactory());
        $this->assertEquals(serialize(new SerializableObject('serializable')), $definition->getArgument(0));
    }

    /** @test */
    public function exportExportableObject(): void
    {
        $object = new StatefulObject('exportable');
        $definition = (new DefaultObjectExporter())->export($object);

        $this->assertEquals(StatefulObject::class, $definition->getClass());
        $this->assertEquals([StatefulObject::class, '__set_state'], $definition->getFactory());
        $this->assertSame(['value' => 'exportable'], $definition->getArgument(0));
    }

    /** @test */
    public function exportExtendedObject(): void
    {
        $object = new ExtendedObject('extended', true);
        $definition = (new DefaultObjectExporter())->export($object);

        $this->assertEquals(ExtendedObject::class, $definition->getClass());
        $this->assertEquals([ExtendedObject::class, '__set_state'], $definition->getFactory());
        $this->assertSame(['name' => 'extended', 'base' => true], $definition->getArgument(0));
    }

    /** @test */
    public function exportCompoundObject(): void
    {
        $object = new CompoundObject('foo', [new SerializableObject('serializable'), new StatefulObject('exportable')]);
        $definition = (new DefaultObjectExporter())->export($object);

        $this->assertEquals(CompoundObject::class, $definition->getClass());
        $this->assertSame([CompoundObject::class, '__set_state'], $definition->getFactory());

        $argument = $definition->getArgument(0);
        $this->assertArrayHasKey('name', $argument);
        $this->assertArrayHasKey('children', $argument);
        $this->assertEquals('foo', $argument['name']);
        $this->assertCount(2, $argument['children']);

        $this->assertInstanceOf(Definition::class, $argument['children'][0]);
        $this->assertEquals(SerializableObject::class, $argument['children'][0]->getClass());
        $this->assertEquals('unserialize', $argument['children'][0]->getFactory());
        $this->assertEquals(serialize(new SerializableObject('serializable')), $argument['children'][0]->getArgument(0));

        $this->assertInstanceOf(Definition::class, $argument['children'][1]);
        $this->assertEquals(StatefulObject::class, $argument['children'][1]->getClass());
        $this->assertEquals([StatefulObject::class, '__set_state'], $argument['children'][1]->getFactory());
        $this->assertEquals(['value' => 'exportable'], $argument['children'][1]->getArgument(0));
    }

    /** @test */
    public function exportObjectWithResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to export resources.');

        $object = new StatefulObject(tmpfile());
        (new DefaultObjectExporter())->export($object);
    }

    /** @test */
    public function exportObjectWitCallback(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not serialize object of the type "%s"', SerializableObject::class));

        $object = new SerializableObject(function() {});
        (new DefaultObjectExporter())->export($object);
    }
}

class CompoundObject implements AnnotationInterface, Exportable
{
    private $name;
    private $children;

    public static function __set_state(array $data): self
    {
        return new self($data['name'], $data['children']);
    }

    public function __construct(string $name, array $children)
    {
        $this->name = $name;
        $this->children = $children;
    }
}

class SerializableObject
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}

class StatefulObject
{
    private $value;

    public static function __set_state(array $data): self
    {
        return new self($data['value']);
    }

    public function __construct($value)
    {
        $this->value = $value;
    }
}

class BaseObject
{
    private $base;

    public function __construct(bool $base)
    {
        $this->base = $base;
    }
}

class ExtendedObject extends BaseObject implements Exportable
{
    private $name;

    public static function __set_state(array $data): self
    {
        return new self($data['name'], $data['base']);
    }

    public function __construct(string $name, bool $base)
    {
        parent::__construct($base);

        $this->name = $name;
    }
}
