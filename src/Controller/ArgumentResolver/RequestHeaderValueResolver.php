<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

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

    public function getArgumentValue(string $name, Request $request, NamedValue $attribute, ArgumentMetadata $metadata)
    {
        if ('array' === $metadata->getType()) {
            return $request->headers->all($name);
        }

        return $request->headers->get($name);
    }
}
