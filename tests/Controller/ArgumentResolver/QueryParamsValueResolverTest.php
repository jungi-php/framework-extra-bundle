<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\QueryParams;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\QueryParamsValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\ForeignAttribute;
use Jungi\FrameworkExtraBundle\Tests\TestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class QueryParamsValueResolverTest extends TestCase
{
    use ExpectDeprecationTrait;

    /** @test */
    public function supports()
    {
        // Attribute
        $resolver = new QueryParamsValueResolver($this->createMock(ConverterInterface::class));

        $request = new Request([], [], ['_controller' => 'FooController']);
        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null, false, [
            new QueryParams()
        ])));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null, false, [
            new ForeignAttribute()
        ])));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', null, false, false, null)));
    }

    /** @test */
    public function requestWithQueryParameters()
    {
        $type = 'stdClass';
        $request = new Request(['foo' => 'bar'], [], ['_controller' => 'FooController']);

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->exactly(1))
            ->method('convert')
            ->with($request->query->all(), $type);

        $resolver = new QueryParamsValueResolver($converter);
        $resolver->resolve($request, new ArgumentMetadata('foo', $type, false, false, null, false, [
            new QueryParams()
        ]))->current();
    }

    /** @test */
    public function nullableArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" cannot be nullable');

        $resolver = new QueryParamsValueResolver($this->createMock(ConverterInterface::class));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', 'stdClass', false, false, null, true, [
            new QueryParams()
        ]))->current();
    }

    /** @test */
    public function argumentWithoutType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" must have the type specified');

        $resolver = new QueryParamsValueResolver($this->createMock(ConverterInterface::class));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', null, false, false, null, false, [
            new QueryParams()
        ]))->current();
    }

    /** @test */
    public function nonObjectArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" must be of concrete class type');

        $resolver = new QueryParamsValueResolver($this->createMock(ConverterInterface::class));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', 'string', false, false, null, false, [
            new QueryParams()
        ]))->current();
    }
}
