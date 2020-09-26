<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestCookieValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestCookieValueResolverTest extends AbstractNamedValueArgumentValueResolverTest
{
    public function argumentTypeSameAsParameterType()
    {
        $this->markTestSkipped('always as string value');
    }

    protected function createAttributeArgumentValueResolver(ConverterInterface $converter, ContainerInterface $attributeLocator): ArgumentValueResolverInterface
    {
        return RequestCookieValueResolver::onAttribute($converter, $attributeLocator);
    }

    protected function createAnnotationArgumentValueResolver(ConverterInterface $converter, ContainerInterface $attributeLocator): ArgumentValueResolverInterface
    {
        return RequestCookieValueResolver::onAnnotation($converter, $attributeLocator);
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
