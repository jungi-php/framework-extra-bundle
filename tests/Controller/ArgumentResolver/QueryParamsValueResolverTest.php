<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\QueryParamsValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\DummyObject;
use Jungi\FrameworkExtraBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class QueryParamsValueResolverTest extends TestCase
{
    /** @test */
    public function supports()
    {
        // Attribute
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\QueryParams()]);
            }
        ));
        $resolver = QueryParamsValueResolver::onAttribute($this->createMock(ConverterInterface::class), $attributeLocator);

        $request = new Request([], [], ['_controller' => 'FooController']);
        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', null, false, false, null)));

        // Dummy
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new DummyObject()]);
            }
        ));
        $resolver = QueryParamsValueResolver::onAttribute($this->createMock(ConverterInterface::class), $attributeLocator);
        $request = new Request([], [], ['_controller' => 'FooController']);

        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));

        // Annotation
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Annotation\QueryParams(['value' => 'foo'])]);
            }
        ));
        $resolver = QueryParamsValueResolver::onAnnotation($this->createMock(ConverterInterface::class), $attributeLocator);

        $request = new Request([], [], ['_controller' => 'FooController']);
        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', null, false, false, null)));
    }

    /** @test */
    public function requestWithQueryParameters()
    {
        $type = 'stdClass';
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\QueryParams()]);
            }
        ));

        $request = new Request(['foo' => 'bar'], [], ['_controller' => 'FooController']);

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with($request->query->all(), $type);

        $resolver = QueryParamsValueResolver::onAttribute($converter, $attributeLocator);
        $resolver->resolve($request, new ArgumentMetadata('foo', $type, false, false, null))->current();
    }

    /** @test */
    public function nullableArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" cannot be nullable');

        $resolver = QueryParamsValueResolver::onAttribute($this->createMock(ConverterInterface::class), new ServiceLocator([]));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', 'stdClass', false, false, null, true))->current();
    }

    /** @test */
    public function argumentWithoutType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" must have the type specified');

        $resolver = QueryParamsValueResolver::onAttribute($this->createMock(ConverterInterface::class), new ServiceLocator([]));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', null, false, false, null))->current();
    }

    /** @test */
    public function nonObjectArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" must be of concrete class type');

        $resolver = QueryParamsValueResolver::onAttribute($this->createMock(ConverterInterface::class), new ServiceLocator([]));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', 'string', false, false, null))->current();
    }
}
