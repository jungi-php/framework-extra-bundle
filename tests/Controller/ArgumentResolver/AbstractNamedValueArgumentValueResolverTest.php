<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\NamedValueArgumentInterface;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\FakeArgumentAnnotation;
use PHPUnit\Framework\TestCase;
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
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAnnotationContainer([$this->createAnnotation('foo')]);
            }
        ));
        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class), $annotationLocator);

        $request = new Request([], [], ['_controller' => 'FooController']);
        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', null, false, false, null)));

        $request = new Request([], [], ['_controller' => 'BarController']);
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));

        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAnnotationContainer([new FakeArgumentAnnotation()]);
            }
        ));
        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class), $annotationLocator);
        $request = new Request([], [], ['_controller' => 'FooController']);

        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', null, false, false, null)));
    }

    /** @test */
    public function parameterConversion()
    {
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAnnotationContainer([$this->createAnnotation('foo')]);
            }
        ));

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with('1992-12-10 23:23:23', \DateTimeImmutable::class);

        $resolver = $this->createArgumentValueResolver($converter, $annotationLocator);
        $request = $this->createRequestWithParameters(array(
            'foo' => '1992-12-10 23:23:23'
        ));
        $request->attributes->set('_controller', 'FooController');

        $resolver->resolve($request, new ArgumentMetadata('foo', \DateTimeImmutable::class, false, false, null))->current();
    }

    /** @test */
    public function argumentWithoutType()
    {
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAnnotationContainer([$this->createAnnotation('foo')]);
            }
        ));

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createArgumentValueResolver($converter, $annotationLocator);
        $request = $this->createRequestWithParameters(array(
            'foo' => 'bar'
        ));
        $request->attributes->set('_controller', 'FooController');

        $resolver->resolve($request, new ArgumentMetadata('foo', null, false, false, null))->current();
    }

    /** @test */
    public function argumentTypeSameAsParameterType()
    {
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAnnotationContainer([$this->createAnnotation('foo')]);
            }
        ));

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createArgumentValueResolver($converter, $annotationLocator);
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

        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAnnotationContainer([$this->createAnnotation('foo')]);
            }
        ));

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->method('convert')
            ->willThrowException(new TypeConversionException('Type conversion failed.'));

        $resolver = $this->createArgumentValueResolver($converter, $annotationLocator);
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

        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class), new ServiceLocator([]));
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', null, true, false, null))->current();
    }

    /** @test */
    public function nonNullableArgument()
    {
        $this->expectException(BadRequestHttpException::class);

        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAnnotationContainer([$this->createAnnotation('foo')]);
            }
        ));

        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class), $annotationLocator);
        $request = new Request([], [], ['_controller' => 'FooController']);

        $resolver->resolve($request, new ArgumentMetadata('foo', 'string', false, false, null))->current();
    }

    /** @test */
    public function nullableArgument()
    {
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAnnotationContainer([$this->createAnnotation('foo')]);
            }
        ));

        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class), $annotationLocator);
        $request = new Request([], [], ['_controller' => 'FooController']);

        $this->assertNull($resolver->resolve($request, new ArgumentMetadata('foo', null, false, false, null, true))->current());
    }

    /** @test */
    public function defaultArgumentValue()
    {
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAnnotationContainer([$this->createAnnotation('foo')]);
            }
        ));

        $defaultValue = 'bar';
        $resolver = $this->createArgumentValueResolver($this->createMock(ConverterInterface::class), $annotationLocator);
        $request = new Request([], [], ['_controller' => 'FooController']);

        $value = $resolver->resolve($request, new ArgumentMetadata('foo', null, false, true, $defaultValue))->current();
        $this->assertEquals($defaultValue, $value);
    }

    protected function createAnnotationContainer(array $annotations): ContainerInterface
    {
        $map = array();
        foreach ($annotations as $annotation) {
            $map[get_class($annotation)] = $annotation;
        }

        return new SimpleContainer($map);
    }

    abstract protected function createArgumentValueResolver(ConverterInterface $converter, ContainerInterface $annotationLocator): ArgumentValueResolverInterface;

    abstract protected function createRequestWithParameters(array $parameters): Request;

    abstract protected function createAnnotation(string $name): NamedValueArgumentInterface;
}
