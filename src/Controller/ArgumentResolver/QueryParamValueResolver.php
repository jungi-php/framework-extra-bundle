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
final class QueryParamValueResolver extends AbstractNamedValueArgumentValueResolver
{
    public static function onAttribute(ConverterInterface $converter, ContainerInterface $attributeLocator): self
    {
        return new self(Attribute\QueryParam::class, $converter, $attributeLocator);
    }

    public static function onAnnotation(ConverterInterface $converter, ContainerInterface $attributeLocator): self
    {
        return new self(Annotation\QueryParam::class, $converter, $attributeLocator);
    }

    protected function getArgumentValue(string $name, Request $request, NamedValue $attribute, ArgumentMetadata $metadata)
    {
        return $request->query->get($name);
    }
}
