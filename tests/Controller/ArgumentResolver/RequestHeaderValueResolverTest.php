<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestHeaderValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestHeaderValueResolverTest extends AbstractNamedValueArgumentValueResolverTest
{
    use ExpectDeprecationTrait;

    public function argumentTypeSameAsParameterType()
    {
        $this->markTestSkipped('always as string value');
    }

    /** @test */
    public function argumentOfArrayType()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createArgumentValueResolver($converter);
        $request = $this->createRequestWithParameters(['foo' => ['one', 'second']]);
        $request->attributes->set('_controller', 'FooController');

        $argumentMetadata =  new ArgumentMetadata('foo', 'array', false, false, null, false, [
            $this->createAttribute('foo')
        ]);
        $this->assertEquals(['one', 'second'], $resolver->resolve($request, $argumentMetadata)->current());
    }

    /**
     * @test
     * @group legacy
     */
    public function deprecationOnAnnotation(): void
    {
        $this->expectDeprecation(sprintf('Since jungi/framework-extra-bundle 1.4: The "%s::%s" method is deprecated, use the constructor instead.', RequestHeaderValueResolver::class, 'onAnnotation'));

        RequestHeaderValueResolver::onAnnotation($this->createMock(ConverterInterface::class), new ServiceLocator([]));
    }

    /**
     * @test
     * @group legacy
     */
    public function deprecationOnAttribute(): void
    {
        $this->expectDeprecation(sprintf('Since jungi/framework-extra-bundle 1.4: The "%s::%s" method is deprecated, use the constructor instead.', RequestHeaderValueResolver::class, 'onAttribute'));

        RequestHeaderValueResolver::onAttribute($this->createMock(ConverterInterface::class), new ServiceLocator([]));
    }

    protected function createArgumentValueResolver(ConverterInterface $converter, ?ContainerInterface $attributeLocator = null): ArgumentValueResolverInterface
    {
        return new RequestHeaderValueResolver($converter, $attributeLocator);
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
