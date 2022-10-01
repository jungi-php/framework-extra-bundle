<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
abstract class AbstractNamedValueArgumentValueResolver implements ArgumentValueResolverInterface
{
    protected static string $attributeClass;

    private ConverterInterface $converter;

    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return (bool) $argument->getAttributes(static::$attributeClass, ArgumentMetadata::IS_INSTANCEOF);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        /** @var NamedValue|null $attribute */
        $attribute = $argument->getAttributes(static::$attributeClass, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;

        if (null === $attribute) {
            return [];
        }

        if ($argument->isVariadic()) {
            throw new \InvalidArgumentException('Variadic arguments are not supported.');
        }

        $namedValueArgument = new NamedValueArgument(
            $attribute->name() ?: $argument->getName(),
            $argument->getType(),
            $attribute
        );
        $value = $this->getArgumentValue($namedValueArgument, $request);

        if (null === $value && $argument->hasDefaultValue()) {
            $value = $argument->getDefaultValue();
        }

        if (null === $value) {
            if ($argument->isNullable()) {
                return [null];
            }

            throw new BadRequestHttpException(sprintf('Argument "%s" cannot be found in the request.', $namedValueArgument->getName()));
        }

        if (null === $argument->getType()) {
            return [$value];
        }

        try {
            return [$this->converter->convert($value, $argument->getType())];
        } catch (TypeConversionException $e) {
            throw new BadRequestHttpException(sprintf('Cannot convert named argument "%s".', $namedValueArgument->getName()), $e);
        }
    }

    abstract protected function getArgumentValue(NamedValueArgument $argument, Request $request): mixed;
}
