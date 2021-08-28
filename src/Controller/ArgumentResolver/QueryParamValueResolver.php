<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\QueryParam;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class QueryParamValueResolver extends AbstractNamedValueArgumentValueResolver
{
    protected static $attributeClass = QueryParam::class;

    /** @deprecated since v1.4, use constructor instead */
    public static function onAttribute(ConverterInterface $converter, ContainerInterface $attributeLocator): self
    {
        trigger_deprecation('jungi/framework-extra-bundle', '1.4', 'The "%s" method is deprecated, use the constructor instead.', __METHOD__);

        return new self($converter, $attributeLocator);
    }

    /** @deprecated since v1.4, use constructor instead */
    public static function onAnnotation(ConverterInterface $converter, ContainerInterface $attributeLocator): self
    {
        trigger_deprecation('jungi/framework-extra-bundle', '1.4', 'The "%s" method is deprecated, use the constructor instead.', __METHOD__);

        return new self($converter, $attributeLocator);
    }

    protected function getArgumentValue(NamedValueArgument $argument, Request $request)
    {
        return $request->query->get($argument->getName());
    }
}
