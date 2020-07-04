<?php

namespace Jungi\FrameworkExtraBundle\Tests\DependencyInjection;

use Jungi\FrameworkExtraBundle\Annotation\AnnotationInterface;
use Jungi\FrameworkExtraBundle\DependencyInjection\DefaultObjectExporter;
use Jungi\FrameworkExtraBundle\DependencyInjection\StatefulObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

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
        $object = new ExportableObject('exportable');
        $definition = (new DefaultObjectExporter())->export($object);

        $this->assertEquals(ExportableObject::class, $definition->getClass());
        $this->assertEquals([ExportableObject::class, 'fromState'], $definition->getFactory());
        $this->assertSame(['name' => 'exportable'], $definition->getArgument(0));
    }

    /** @test */
    public function exportExtendedObject(): void
    {
        $object = new ExtendedObject('extended', true);
        $definition = (new DefaultObjectExporter())->export($object);

        $this->assertEquals(ExtendedObject::class, $definition->getClass());
        $this->assertEquals([ExtendedObject::class, 'fromState'], $definition->getFactory());
        $this->assertSame(['name' => 'extended', 'base' => true], $definition->getArgument(0));
    }

    /** @test */
    public function exportCompoundObject(): void
    {
        $object = new CompoundObject('foo', [new SerializableObject('serializable'), new ExportableObject('exportable')]);
        $definition = (new DefaultObjectExporter())->export($object);

        $this->assertEquals(CompoundObject::class, $definition->getClass());
        $this->assertSame([CompoundObject::class, 'fromState'], $definition->getFactory());

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
        $this->assertEquals(ExportableObject::class, $argument['children'][1]->getClass());
        $this->assertEquals([ExportableObject::class, 'fromState'], $argument['children'][1]->getFactory());
        $this->assertEquals(['name' => 'exportable'], $argument['children'][1]->getArgument(0));
    }

    /** @test */
    public function exportObjectWithResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to dump an object that contains resources.');

        $object = new ObjectWithResource(tmpfile());
        (new DefaultObjectExporter())->export($object);
    }
}

class CompoundObject implements AnnotationInterface, StatefulObject
{
    private $name;
    private $children;

    public static function fromState(array $data): self
    {
        return new self($data['name'], $data['children']);
    }

    public function __construct(string $name, array $children)
    {
        $this->name = $name;
        $this->children = $children;
    }
}

class ObjectWithResource implements StatefulObject
{
    private $file;

    public static function fromState(array $data): self
    {
        return new self($data['file']);
    }

    public function __construct($file)
    {
        $this->file = $file;
    }
}

class SerializableObject
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

class ExportableObject implements StatefulObject
{
    private $name;

    public static function fromState(array $data): self
    {
        return new self($data['name']);
    }

    public function __construct(string $name)
    {
        $this->name = $name;
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

class ExtendedObject extends BaseObject implements StatefulObject
{
    private $name;

    public static function fromState(array $data): self
    {
        return new self($data['name'], $data['base']);
    }

    public function __construct(string $name, bool $base)
    {
        parent::__construct($base);

        $this->name = $name;
    }
}
