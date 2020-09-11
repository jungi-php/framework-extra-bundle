<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\NamedValueArgument;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\DummyObject;
use Jungi\FrameworkExtraBundle\Tests\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class AbstractNamedValueArgumentValueResolverTest extends TestCase
{
    /** @test */
    public function supports()
    {
        // Attribute
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([$this->createAttribute('foo')]);
            }
        ));
        $resolver = $this->createAttributeArgumentValueResolver($this->createMock(ConverterInterface::class), $attributeLocator);

        $request = new Request([], [], ['_controller' => 'FooController']);
        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', null, false, false, null)));

        $request = new Request([], [], ['_controller' => 'BarController']);
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));

        // Dummy
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new DummyObject()]);
            }
        ));
        $resolver = $this->createAttributeArgumentValueResolver($this->createMock(ConverterInterface::class), $attributeLocator);
        $request = new Request([], [], ['_controller' => 'FooController']);

        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));

        // Annotation
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([$this->createAnnotation('foo')]);
            }
        ));
        $resolver = $this->createAnnotationArgumentValueResolver($this->createMock(ConverterInterface::class), $attributeLocator);

        $request = new Request([], [], ['_controller' => 'FooController']);
        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', null, false, false, null)));
    }

    /** @test */
    public function parameterConversion()
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([$this->createAttribute('foo')]);
            }
        ));

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with('1992-12-10 23:23:23', \DateTimeImmutable::class);

        $resolver = $this->createAttributeArgumentValueResolver($converter, $attributeLocator);
        $request = $this->createRequestWithParameters(array(
            'foo' => '1992-12-10 23:23:23'
        ));
        $request->attributes->set('_controller', 'FooController');

        $resolver->resolve($request, new ArgumentMetadata('foo', \DateTimeImmutable::class, false, false, null))->current();
    }

    /** @test */
    public function argumentWithoutType()
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([$this->createAttribute('foo')]);
            }
        ));

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createAttributeArgumentValueResolver($converter, $attributeLocator);
        $request = $this->createRequestWithParameters(array(
            'foo' => 'bar'
        ));
        $request->attributes->set('_controller', 'FooController');

        $resolver->resolve($request, new ArgumentMetadata('foo', null, false, false, null))->current();
    }

    /** @test */
    public function argumentTypeSameAsParameterType()
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([$this->createAttribute('foo')]);
            }
        ));

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createAttributeArgumentValueResolver($converter, $attributeLocator);
        $request = $this->createRequestWithParameters(array(
            'foo' => 123
        ));
        $request->attributes->set('_controller', 'FooController');

        $resolver->resolve($request, new ArgumentMetadata('foo', 'int', false, false, null))->current();
    }

    /** @test */
    public function invalidParameter()
    {
        $this->expectException(BadRequestHttpException::class);

        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([$this->createAttribute('foo')]);
            }
        ));

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->method('convert')
            ->willThrowException(new TypeConversionException('Type conversion failed.'));

        $resolver = $this->createAttributeArgumentValueResolver($converter, $attributeLocator);
        $request = $this->createRequestWithParameters(array(
            'foo' => 'bar'
        ));
        $request->attributes->set('_controller', 'FooController');

        $resolver->resolve($request, new ArgumentMetadata('foo', \DateTimeImmutable::class, false, false, null))->current();
    }

    /** @test */
    public function variadicArgument()
    {
        $this->expectException(\InvalidArgumentException::class);

        $resolver = $this->createAttributeArgumentValueResolver($this->createMock(ConverterInterface::class), new ServiceLocator([]));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', null, true, false, null))->current();
    }

    /** @test */
    public function nonNullableArgument()
    {
        $this->expectException(BadRequestHttpException::class);

        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([$this->createAttribute('foo')]);
            }
        ));

        $resolver = $this->createAttributeArgumentValueResolver($this->createMock(ConverterInterface::class), $attributeLocator);
        $request = new Request([], [], ['_controller' => 'FooController']);

        $resolver->resolve($request, new ArgumentMetadata('foo', 'string', false, false, null))->current();
    }

    /** @test */
    public function nullableArgument()
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([$this->createAttribute('foo')]);
            }
        ));

        $resolver = $this->createAttributeArgumentValueResolver($this->createMock(ConverterInterface::class), $attributeLocator);
        $request = new Request([], [], ['_controller' => 'FooController']);

        $this->assertNull($resolver->resolve($request, new ArgumentMetadata('foo', null, false, false, null, true))->current());
    }

    /** @test */
    public function defaultArgumentValue()
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([$this->createAttribute('foo')]);
            }
        ));

        $defaultValue = 'bar';
        $resolver = $this->createAttributeArgumentValueResolver($this->createMock(ConverterInterface::class), $attributeLocator);
        $request = new Request([], [], ['_controller' => 'FooController']);

        $value = $resolver->resolve($request, new ArgumentMetadata('foo', null, false, true, $defaultValue))->current();
        $this->assertEquals($defaultValue, $value);
    }

    abstract protected function createAttributeArgumentValueResolver(ConverterInterface $converter, ContainerInterface $attributeLocator): ArgumentValueResolverInterface;

    abstract protected function createAnnotationArgumentValueResolver(ConverterInterface $converter, ContainerInterface $attributeLocator): ArgumentValueResolverInterface;

    abstract protected function createRequestWithParameters(array $parameters): Request;

    abstract protected function createAttribute(string $name): NamedValueArgument;

    abstract protected function createAnnotation(string $name): NamedValueArgument;
}
