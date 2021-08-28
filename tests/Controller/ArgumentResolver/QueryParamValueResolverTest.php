<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\QueryParamValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class QueryParamValueResolverTest extends AbstractNamedValueArgumentValueResolverTest
{
    use ExpectDeprecationTrait;

    /**
     * @test
     * @group legacy
     */
    public function deprecationOnAnnotation(): void
    {
        $this->expectDeprecation(sprintf('Since jungi/framework-extra-bundle 1.4: The "%s::%s" method is deprecated, use the constructor instead.', QueryParamValueResolver::class, 'onAnnotation'));

        QueryParamValueResolver::onAnnotation($this->createMock(ConverterInterface::class), new ServiceLocator([]));
    }

    /**
     * @test
     * @group legacy
     */
    public function deprecationOnAttribute(): void
    {
        $this->expectDeprecation(sprintf('Since jungi/framework-extra-bundle 1.4: The "%s::%s" method is deprecated, use the constructor instead.', QueryParamValueResolver::class, 'onAttribute'));

        QueryParamValueResolver::onAttribute($this->createMock(ConverterInterface::class), new ServiceLocator([]));
    }

    protected function createArgumentValueResolver(ConverterInterface $converter, ?ContainerInterface $attributeLocator = null): ArgumentValueResolverInterface
    {
        return new QueryParamValueResolver($converter, $attributeLocator);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        return new Request($parameters);
    }

    protected function createAttribute(string $name): NamedValue
    {
        return new Attribute\QueryParam($name);
    }

    protected function createAnnotation(string $name): NamedValue
    {
        return new Annotation\QueryParam(['name' => $name]);
    }
}
