<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\QueryParams;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class QueryParamsValueResolver implements ArgumentValueResolverInterface
{
    private ConverterInterface $converter;

    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return (bool) $argument->getAttributes(QueryParams::class, ArgumentMetadata::IS_INSTANCEOF);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        if (!$argument->getAttributes(QueryParams::class, ArgumentMetadata::IS_INSTANCEOF)) {
            return [];
        }

        if (!$argument->getType()) {
            throw new \InvalidArgumentException(sprintf('Argument "%s" must have the type specified for the request query conversion.', $argument->getName()));
        }
        if ($argument->isNullable()) {
            throw new \InvalidArgumentException(sprintf('Argument "%s" cannot be nullable for the request query conversion.', $argument->getName()));
        }
        if (!class_exists($argument->getType())) {
            throw new \InvalidArgumentException(sprintf('Argument "%s" must be of concrete class type for the request query conversion.', $argument->getName()));
        }

        return [$this->converter->convert($request->query->all(), $argument->getType())];
    }
}
