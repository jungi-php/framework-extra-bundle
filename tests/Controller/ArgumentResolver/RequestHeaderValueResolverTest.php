<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
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
class RequestHeaderValueResolverTest extends AbstractNamedValueArgumentValueResolverTest
{
    public function argumentTypeSameAsParameterType()
    {
        $this->markTestSkipped('always as string value');
    }

    /** @test */
    public function argumentOfArrayType()
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
        $request = $this->createRequestWithParameters(['foo' => ['one', 'second']]);
        $request->attributes->set('_controller', 'FooController');

        $argumentMetadata =  new ArgumentMetadata('foo', 'array', false, false, null);

        $this->assertEquals(['one', 'second'], $resolver->resolve($request, $argumentMetadata)->current());
    }

    protected function createAttributeArgumentValueResolver(ConverterInterface $converter, ContainerInterface $attributeLocator): ArgumentValueResolverInterface
    {
        return RequestHeaderValueResolver::onAttribute($converter, $attributeLocator);
    }

    protected function createAnnotationArgumentValueResolver(ConverterInterface $converter, ContainerInterface $attributeLocator): ArgumentValueResolverInterface
    {
        return RequestHeaderValueResolver::onAnnotation($converter, $attributeLocator);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        $request = new Request();
        $request->headers->replace($parameters);

        return $request;
    }

    protected function createAttribute(string $name): NamedValue
    {
        return new Attribute\RequestHeader($name);
    }

    protected function createAnnotation(string $name): NamedValue
    {
        return new Annotation\RequestHeader(['name' => $name]);
    }
}
