<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestCookieValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestCookieValueResolverTest extends AbstractNamedValueArgumentValueResolverTest
{
    use ExpectDeprecationTrait;

    public function argumentTypeSameAsParameterType()
    {
        $this->markTestSkipped('always as string value');
    }

    /**
     * @test
     * @group legacy
     */
    public function deprecationOnAnnotation(): void
    {
        $this->expectDeprecation(sprintf('Since jungi/framework-extra-bundle 1.4: The "%s::%s" method is deprecated, use the constructor instead.', RequestCookieValueResolver::class, 'onAnnotation'));

        RequestCookieValueResolver::onAnnotation($this->createMock(ConverterInterface::class), new ServiceLocator([]));
    }

    /**
     * @test
     * @group legacy
     */
    public function deprecationOnAttribute(): void
    {
        $this->expectDeprecation(sprintf('Since jungi/framework-extra-bundle 1.4: The "%s::%s" method is deprecated, use the constructor instead.', RequestCookieValueResolver::class, 'onAttribute'));

        RequestCookieValueResolver::onAttribute($this->createMock(ConverterInterface::class), new ServiceLocator([]));
    }

    protected function createArgumentValueResolver(ConverterInterface $converter, ?ContainerInterface $attributeLocator = null): ArgumentValueResolverInterface
    {
        return new RequestCookieValueResolver($converter, $attributeLocator);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        return new Request([], [], [], $parameters);
    }

    protected function createAttribute(string $name): NamedValue
    {
        return new Attribute\RequestCookie($name);
    }

    protected function createAnnotation(string $name): NamedValue
    {
        return new Annotation\RequestCookie(['name' => $name]);
    }
}
