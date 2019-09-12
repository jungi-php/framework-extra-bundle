<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\ClassMethodAnnotationRegistry;
use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\FakeArgumentAnnotation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class AbstractRequestParamValueResolverTest extends TestCase
{
    /** @test */
    public function supports()
    {
        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class));
        $request = new Request();

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [$this->createAnnotation('foo')]));

        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', null, false, false, null)));
        $this->assertFalse($resolver->supports(new Request(), new ArgumentMetadata('foo', null, false, false, null)));

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [new FakeArgumentAnnotation()]));

        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));
    }

    /** @test */
    public function parameterConversion()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with('1992-12-10 23:23:23', \DateTimeImmutable::class);

        $resolver = $this->createArgumentValueResolver($converter);
        $request = $this->createRequestWithParameters(array(
            'foo' => '1992-12-10 23:23:23'
        ));

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            $this->createAnnotation('foo'),
        ]));

        $resolver->resolve($request, new ArgumentMetadata('foo', \DateTimeImmutable::class, false, false, null))->current();
    }

    /** @test */
    public function argumentWithoutType()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createArgumentValueResolver($converter);
        $request = $this->createRequestWithParameters(array(
            'foo' => 'bar'
        ));

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            $this->createAnnotation('foo'),
        ]));

        $resolver->resolve($request, new ArgumentMetadata('foo', null, false, false, null))->current();
    }

    /** @test */
    public function argumentTypeSameAsParameterType()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createArgumentValueResolver($converter);
        $request = $this->createRequestWithParameters(array(
            'foo' => 123
        ));

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            $this->createAnnotation('foo'),
        ]));

        $resolver->resolve($request, new ArgumentMetadata('foo', 'int', false, false, null))->current();
    }

    /** @test */
    public function invalidParameter()
    {
        $this->expectException(BadRequestHttpException::class);

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->method('convert')
            ->willThrowException(new TypeConversionException('Type conversion failed.'));

        $resolver = $this->createArgumentValueResolver($converter);
        $request = $this->createRequestWithParameters(array(
            'foo' => 'bar'
        ));

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            $this->createAnnotation('foo'),
        ]));

        $resolver->resolve($request, new ArgumentMetadata('foo', \DateTimeImmutable::class, false, false, null))->current();
    }

    /** @test */
    public function variadicArgument()
    {
        $this->expectException(\InvalidArgumentException::class);

        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class));
        $request = new Request();

        $resolver->resolve($request, new ArgumentMetadata('foo', null, true, false, null))->current();
    }

    /** @test */
    public function nonNullableArgument()
    {
        $this->expectException(BadRequestHttpException::class);

        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class));
        $request = new Request();

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            $this->createAnnotation('foo'),
        ]));

        $resolver->resolve($request, new ArgumentMetadata('foo', 'string', false, false, null))->current();
    }

    /** @test */
    public function nullableArgument()
    {
        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class));
        $request = new Request();

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            $this->createAnnotation('foo'),
        ]));

        $this->assertNull($resolver->resolve($request, new ArgumentMetadata('foo', null, false, false, null, true))->current());
    }

    /** @test */
    public function defaultArgumentValue()
    {
        $defaultValue = 'bar';
        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class));
        $request = new Request();

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            $this->createAnnotation('foo'),
        ]));

        $value = $resolver->resolve($request, new ArgumentMetadata('foo', null, false, true, $defaultValue))->current();

        $this->assertEquals($defaultValue, $value);
    }

    abstract protected function createArgumentValueResolver(ConverterInterface $converter): ArgumentValueResolverInterface;

    abstract protected function createRequestWithParameters(array $parameters): Request;

    abstract protected function createAnnotation(string $name): RequestParam;
}
