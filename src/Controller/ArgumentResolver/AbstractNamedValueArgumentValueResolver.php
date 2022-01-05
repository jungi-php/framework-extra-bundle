<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Utils\TypeUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
abstract class AbstractNamedValueArgumentValueResolver implements ArgumentValueResolverInterface
{
    /** @var string */
    protected static $attributeClass;
    
    private $converter;

    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return (bool) $argument->getAttributes(static::$attributeClass, ArgumentMetadata::IS_INSTANCEOF);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->isVariadic()) {
            throw new \InvalidArgumentException('Variadic arguments are not supported.');
        }

        /** @var NamedValue $attribute */
        $attribute = $argument->getAttributes(static::$attributeClass, ArgumentMetadata::IS_INSTANCEOF)[0];
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
                yield null; return;
            }

            throw new BadRequestHttpException(sprintf('Argument "%s" cannot be found in the request.', $namedValueArgument->getName()));
        }

        if (null === $argument->getType() || TypeUtils::isValueOfType($value, $argument->getType())) {
            yield $value; return;
        }

        try {
            yield $this->converter->convert($value, $argument->getType());
        } catch (TypeConversionException $e) {
            throw new BadRequestHttpException(sprintf('Cannot convert named argument "%s".', $namedValueArgument->getName()), $e);
        }
    }

    abstract protected function getArgumentValue(NamedValueArgument $argument, Request $request);
}
