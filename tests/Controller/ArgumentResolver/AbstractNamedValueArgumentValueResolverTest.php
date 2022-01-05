<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\ForeignAttribute;
use Jungi\FrameworkExtraBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class AbstractNamedValueArgumentValueResolverTest extends TestCase
{
    /** @test */
    public function supports()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = $this->createArgumentValueResolver($converter);

        $request = new Request([], [], ['_controller' => 'FooController']);
        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null, false, [
            $this->createAttribute('foo')
        ])));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null, false, [
            new ForeignAttribute()
        ])));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', null, false, false, null)));
    }

    /** @test */
    public function parameterConversion()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->exactly(1))
            ->method('convert')
            ->with('1992-12-10 23:23:23', \DateTimeImmutable::class);

        $request = $this->createRequestWithParameters(array(
            'foo' => '1992-12-10 23:23:23'
        ));
        $request->attributes->set('_controller', 'FooController');

        $resolver = $this->createArgumentValueResolver($converter);
        $resolver->resolve($request, new ArgumentMetadata('foo', \DateTimeImmutable::class, false, false, null, false, [
            $this->createAttribute('foo')
        ]))->current();
    }

    /** @test */
    public function argumentWithoutType()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $request = $this->createRequestWithParameters(array(
            'foo' => 'bar'
        ));
        $request->attributes->set('_controller', 'FooController');

        $resolver = $this->createArgumentValueResolver($converter);
        $resolver->resolve($request, new ArgumentMetadata('foo', null, false, false, null, false, [
            $this->createAttribute('foo')
        ]))->current();
    }

    /** @test */
    public function argumentTypeSameAsParameterType()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $request = $this->createRequestWithParameters(array(
            'foo' => 123
        ));
        $request->attributes->set('_controller', 'FooController');

        $resolver = $this->createArgumentValueResolver($converter);
        $resolver->resolve($request, new ArgumentMetadata('foo', 'int', false, false, null, false, [
            $this->createAttribute('foo')
        ]))->current();
    }

    /** @test */
    public function invalidParameter()
    {
        $this->expectException(BadRequestHttpException::class);

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->method('convert')
            ->willThrowException(new TypeConversionException('Type conversion failed.'));

        $request = $this->createRequestWithParameters(array(
            'foo' => 'bar'
        ));
        $request->attributes->set('_controller', 'FooController');

        $resolver = $this->createArgumentValueResolver($converter);
        $resolver->resolve($request, new ArgumentMetadata('foo', \DateTimeImmutable::class, false, false, null, false, [
            $this->createAttribute('foo')
        ]))->current();
    }

    /** @test */
    public function variadicArgument()
    {
        $this->expectException(\InvalidArgumentException::class);

        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', null, true, false, null, false, [
            $this->createAttribute('foo')
        ]))->current();
    }

    /** @test */
    public function nonNullableArgument()
    {
        $this->expectException(BadRequestHttpException::class);

        $request = new Request([], [], ['_controller' => 'FooController']);

        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class));
        $resolver->resolve($request, new ArgumentMetadata('foo', 'string', false, false, null, false, [
            $this->createAttribute('foo')
        ]))->current();
    }

    /** @test */
    public function nullableArgument()
    {
        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class));
        $request = new Request([], [], ['_controller' => 'FooController']);

        $this->assertNull($resolver->resolve($request, new ArgumentMetadata('foo', null, false, false, null, true, [
            $this->createAttribute('foo')
        ]))->current());
    }

    /** @test */
    public function defaultArgumentValue()
    {
        $defaultValue = 'bar';
        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class));
        $request = new Request([], [], ['_controller' => 'FooController']);

        $value = $resolver->resolve($request, new ArgumentMetadata('foo', null, false, true, $defaultValue, false, [
            $this->createAttribute('foo')
        ]))->current();
        $this->assertEquals($defaultValue, $value);
    }

    abstract protected function createArgumentValueResolver(ConverterInterface $converter): ArgumentValueResolverInterface;

    abstract protected function createRequestWithParameters(array $parameters): Request;

    abstract protected function createAttribute(string $name): NamedValue;
}
