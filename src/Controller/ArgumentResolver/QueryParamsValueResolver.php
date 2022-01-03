<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\QueryParams;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class QueryParamsValueResolver implements ArgumentValueResolverInterface
{
    private $converter;
    private $attributeLocator;

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

    public function __construct(ConverterInterface $converter, ?ContainerInterface $attributeLocator = null)
    {
        $this->converter = $converter;
        $this->attributeLocator = $attributeLocator;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if ($argument->getAttributes(QueryParams::class, ArgumentMetadata::IS_INSTANCEOF)) {
            return true;
        }

        if (null === $this->attributeLocator) {
            return false;
        }

        if (null === $controller = RequestUtils::getControllerAsCallableString($request)) {
            return false;
        }

        $id = $controller.'$'.$argument->getName();

        return $this->attributeLocator->has($id) && $this->attributeLocator->get($id)->has(QueryParams::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$argument->getType()) {
            throw new \InvalidArgumentException(sprintf('Argument "%s" must have the type specified for the request query conversion.', $argument->getName()));
        }
        if ($argument->isNullable()) {
            throw new \InvalidArgumentException(sprintf('Argument "%s" cannot be nullable for the request query conversion.', $argument->getName()));
        }
        if (!class_exists($argument->getType())) {
            throw new \InvalidArgumentException(sprintf('Argument "%s" must be of concrete class type for the request query conversion.', $argument->getName()));
        }

        yield $this->converter->convert($request->query->all(), $argument->getType());
    }
}
