<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestFieldAnnotationInterface;
use Jungi\FrameworkExtraBundle\Annotation\RequestHeader;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestHeaderValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestHeaderValueResolverTest extends AbstractRequestFieldValueResolverTest
{
    public function argumentTypeSameAsParameterType()
    {
        $this->markTestSkipped('always as string value');
    }

    /** @test */
    public function argumentOfArrayType()
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
        $request = $this->createRequestWithParameters(['foo' => ['one', 'second']]);
        $request->attributes->set('_controller', 'FooController');

        $argumentMetadata =  new ArgumentMetadata('foo', 'array', false, false, null);

        $this->assertEquals(['one', 'second'], $resolver->resolve($request, $argumentMetadata)->current());
    }

    protected function createArgumentValueResolver(ConverterInterface $converter, ContainerInterface $annotationLocator): ArgumentValueResolverInterface
    {
        return new RequestHeaderValueResolver($converter, $annotationLocator);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        $request = new Request();
        $request->headers->replace($parameters);

        return $request;
    }

    protected function createAnnotation(string $name): RequestFieldAnnotationInterface
    {
        return new RequestHeader(array('argument' => $name, 'field' => $name));
    }
}
