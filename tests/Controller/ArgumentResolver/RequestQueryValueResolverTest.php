<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\ClassMethodAnnotationRegistry;
use Jungi\FrameworkExtraBundle\Annotation\RequestQuery;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestQueryValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestQueryValueResolverTest extends TestCase
{
    /** @test */
    public function supports()
    {
        $resolver = new RequestQueryValueResolver($this->createMock(ConverterInterface::class));

        $request = new Request();
        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [new RequestQuery(['value' => 'foo'])]));

        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', null, false, false, null)));
    }

    /** @test */
    public function requestWithQueryParameters()
    {
        $type = 'stdClass';

        $request = new Request(['foo' => 'bar']);
        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [new RequestQuery(['value' => 'foo'])]));

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with($request->query->all(), $type);

        $resolver = new RequestQueryValueResolver($converter);

        $resolver->resolve($request, new ArgumentMetadata('foo', $type, false, false, null))->current();
    }

    /** @test */
    public function nullableArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" cannot be nullable');

        $resolver = new RequestQueryValueResolver($this->createMock(ConverterInterface::class));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', 'stdClass', false, false, null, true))->current();
    }

    /** @test */
    public function argumentWithoutType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" must have the type specified');

        $resolver = new RequestQueryValueResolver($this->createMock(ConverterInterface::class));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', null, false, false, null))->current();
    }

    /** @test */
    public function nonObjectArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" must be of concrete class type');

        $resolver = new RequestQueryValueResolver($this->createMock(ConverterInterface::class));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', 'string', false, false, null))->current();
    }
}
