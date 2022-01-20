<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\AbstractNamedValueArgumentValueResolver;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\NamedValueArgument;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\ForeignAttribute;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class AbstractNamedValueArgumentValueResolverTest extends TestCase
{
    /** @test */
    public function supports()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new DummyRequestAttributeValueResolver($converter);
        $request = new Request();

        $argument = new ArgumentMetadata('foo', null, false, false, null, false, [
            new DummyRequestAttribute('foo')
        ]);
        $this->assertTrue($resolver->supports($request, $argument));

        $argument = new ArgumentMetadata('foo', null, false, false, null, false, [
            new ForeignAttribute()
        ]);
        $this->assertFalse($resolver->supports($request, $argument));

        $argument = new ArgumentMetadata('bar', null, false, false, null);
        $this->assertFalse($resolver->supports($request, $argument));
    }

    /**
     * @test
     * @dataProvider provideArgumentParameterNames
     */
    public function parameterIsConverted(string $argumentName, ?string $parameterName)
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with('1992-12-10 23:23:23', \DateTimeImmutable::class);

        $resolver = new DummyRequestAttributeValueResolver($converter);

        $request = new Request([], [], [
            $parameterName ?: $argumentName => '1992-12-10 23:23:23'
        ]);
        $argument = new ArgumentMetadata($argumentName, \DateTimeImmutable::class, false, false, null, false, [
            new DummyRequestAttribute($parameterName)
        ]);

        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function parameterWithoutArgumentTypeIsNotConverted()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = new DummyRequestAttributeValueResolver($converter);
        $request = new Request([], [], [
            'foo' => 'bar'
        ]);
        $argument = new ArgumentMetadata('foo', null, false, false, null, false, [
            new DummyRequestAttribute('foo')
        ]);

        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function parameterConversionFails()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Cannot convert named argument "foo"');

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->method('convert')
            ->willThrowException(new TypeConversionException('Type conversion failed.'));

        $resolver = new DummyRequestAttributeValueResolver($converter);
        $request = new Request([], [], [
            'foo' => 'bar'
        ]);
        $argument = new ArgumentMetadata('foo', \DateTimeImmutable::class, false, false, null, false, [
            new DummyRequestAttribute('foo')
        ]);

        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function resolveForVariadicArgumentFails()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Variadic arguments are not supported');

        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new DummyRequestAttributeValueResolver($converter);
        $request = new Request();
        $argument = new ArgumentMetadata('foo', null, true, false, null, false, [
            new DummyRequestAttribute('foo')
        ]);

        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function resolveForNotNullableArgumentOnMissingParameterFails()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Argument "foo" cannot be found in the request');

        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new DummyRequestAttributeValueResolver($converter);
        $request = new Request();
        $argument = new ArgumentMetadata('foo', 'string', false, false, null, false, [
            new DummyRequestAttribute('foo')
        ]);

        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function nullableArgumentOnMissingParameterIsResolvedToNullValue()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new DummyRequestAttributeValueResolver($converter);
        $request = new Request();
        $argument = new ArgumentMetadata('foo', null, false, false, null, true, [
            new DummyRequestAttribute('foo')
        ]);

        $this->assertNull($resolver->resolve($request, $argument)->current());
    }

    /** @test */
    public function argumentOnMissingParameterIsResolvedToDefaultValue()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new DummyRequestAttributeValueResolver($converter);
        $request = new Request();
        $argument = new ArgumentMetadata('foo', null, false, true, 'bar', false, [
            new DummyRequestAttribute('foo')
        ]);

        $this->assertEquals('bar', $resolver->resolve($request, $argument)->current());
    }

    public function provideArgumentParameterNames(): iterable
    {
        yield 'parameter with name' => ['foo', 'bar'];
        yield 'parameter without name' => ['bar', null];
    }
}

#[\Attribute]
class DummyRequestAttribute implements NamedValue
{
    public function __construct(private ?string $name = null) {}

    public function name(): ?string
    {
        return $this->name;
    }
}

class DummyRequestAttributeValueResolver extends AbstractNamedValueArgumentValueResolver
{
    protected static string $attributeClass = DummyRequestAttribute::class;

    protected function getArgumentValue(NamedValueArgument $argument, Request $request): mixed
    {
        return $request->attributes->get($argument->getName());
    }
}