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
final class RequestHeaderValueResolver extends AbstractNamedValueArgumentValueResolver
{
    public static function onAttribute(ConverterInterface $converter, ContainerInterface $attributeLocator): self
    {
        return new self(Attribute\RequestHeader::class, $converter, $attributeLocator);
    }

    public static function onAnnotation(ConverterInterface $converter, ContainerInterface $attributeLocator): self
    {
        return new self(Annotation\RequestHeader::class, $converter, $attributeLocator);
    }

    protected function getArgumentValue(NamedValueArgument $argument, Request $request)
    {
        if ('array' === $argument->getType()) {
            return $request->headers->all($argument->getName());
        }

        return $request->headers->get($argument->getName());
    }
}
