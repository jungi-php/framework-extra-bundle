<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\QueryParamValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class QueryParamValueResolverTest extends AbstractNamedValueArgumentValueResolverTest
{
    protected function createAttributeArgumentValueResolver(ConverterInterface $converter, ContainerInterface $container): ArgumentValueResolverInterface
    {
        return QueryParamValueResolver::onAttribute($converter, $container);
    }

    protected function createAnnotationArgumentValueResolver(ConverterInterface $converter, ContainerInterface $container): ArgumentValueResolverInterface
    {
        return QueryParamValueResolver::onAnnotation($converter, $container);
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
