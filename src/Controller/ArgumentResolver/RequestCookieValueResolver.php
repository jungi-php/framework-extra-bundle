<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestCookieValueResolver extends AbstractNamedValueArgumentValueResolver
{
    public static function onAttribute(ConverterInterface $converter, ContainerInterface $attributeLocator): self
    {
        return new self(Attribute\RequestCookie::class, $converter, $attributeLocator);
    }

    public static function onAnnotation(ConverterInterface $converter, ContainerInterface $attributeLocator): self
    {
        return new self(Annotation\RequestCookie::class, $converter, $attributeLocator);
    }

    protected function getArgumentValue(NamedValueArgument $argument, Request $request)
    {
        return $request->cookies->get($argument->getName());
    }
}
