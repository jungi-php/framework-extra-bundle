<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\QueryParams;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\QueryParamsValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\ForeignAttribute;
use PHPUnit\Framework\TestCase;
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
        $resolver = new QueryParamsValueResolver($this->createMock(ConverterInterface::class));
        $request = new Request();

        $argument = new ArgumentMetadata('foo', null, false, false, null, false, [
            new QueryParams()
        ]);
        $this->assertTrue($resolver->supports($request, $argument));

        $argument = new ArgumentMetadata('foo', null, false, false, null, false, [
            new ForeignAttribute()
        ]);
        $this->assertFalse($resolver->supports($request, $argument));

        $argument = new ArgumentMetadata('bar', null, false, false, null);
        $this->assertFalse($resolver->supports($request, $argument));
    }

    /** @test */
    public function parameterIsConverted()
    {
        $request = new Request(['foo' => 'bar']);

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with($request->query->all(), 'stdClass')
            ->willReturn(new \stdClass());

        $resolver = new QueryParamsValueResolver($converter);
        $argument = new ArgumentMetadata('foo', 'stdClass', false, false, null, false, [
            new QueryParams()
        ]);

        $values = $resolver->resolve($request, $argument);

        $this->assertCount(1, $values);
        $this->assertInstanceOf('stdClass', $values[0]);
    }

    /** @test */
    public function resolveForArgumentWithoutAttributeIsIgnored()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = new QueryParamsValueResolver($converter);
        $request = new Request();
        $argument = new ArgumentMetadata('foo', 'stdClass', false, false, null, false);

        $this->assertEmpty($resolver->resolve($request, $argument));
    }

    /** @test */
    public function resolveForNullableArgumentFails()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" cannot be nullable');

        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new QueryParamsValueResolver($converter);
        $request = new Request();
        $argument = new ArgumentMetadata('foo', 'stdClass', false, false, null, true, [
            new QueryParams()
        ]);

        $resolver->resolve($request, $argument);
    }

    /** @test */
    public function resolveForArgumentWithoutTypeFails()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" must have the type specified');

        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new QueryParamsValueResolver($converter);
        $request = new Request();
        $argument = new ArgumentMetadata('foo', null, false, false, null, false, [
            new QueryParams()
        ]);

        $resolver->resolve($request, $argument);
    }

    /** @test */
    public function resolveForArgumentWithNoConcreteClassTypeFails()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" must be of concrete class type');

        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new QueryParamsValueResolver($converter);
        $request = new Request();
        $argument = new ArgumentMetadata('foo', 'string', false, false, null, false, [
            new QueryParams()
        ]);

        $resolver->resolve($request, $argument);
    }
}
