<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\NamedValueArgument;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Utils\TypeUtils;
use Psr\Container\ContainerInterface;
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
    private $attributeClass;
    private $converter;
    private $attributeLocator;

    protected function __construct(string $attributeClass, ConverterInterface $converter, ContainerInterface $attributeLocator)
    {
        if (!is_subclass_of($attributeClass, NamedValueArgument::class)) {
            throw new \InvalidArgumentException(sprintf('Expected a subclass of "%s", got: "%s".', NamedValueArgument::class, $attributeClass));
        }

        $this->attributeClass = $attributeClass;
        $this->converter = $converter;
        $this->attributeLocator = $attributeLocator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (null === $controller = RequestUtils::getControllerAsCallableString($request)) {
            return false;
        }

        $id = $controller.'$'.$argument->getName();

        return $this->attributeLocator->has($id) && $this->attributeLocator->get($id)->has($this->attributeClass);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if ($argument->isVariadic()) {
            throw new \InvalidArgumentException('Variadic arguments are not supported.');
        }

        $id = RequestUtils::getControllerAsCallableString($request).'$'.$argument->getName();
        $attribute = $this->attributeLocator->get($id)->get($this->attributeClass);

        $name = $attribute->name() ?: $argument->getName();
        $value = $this->getArgumentValue($name, $request, $attribute, $argument);

        if (null === $value && $argument->hasDefaultValue()) {
            $value = $argument->getDefaultValue();
        }

        if (null === $value) {
            if ($argument->isNullable()) {
                yield null; return;
            }

            throw new BadRequestHttpException(sprintf('Argument "%s" cannot be found in the request.', $name));
        }

        if (null === $argument->getType() || TypeUtils::isValueOfType($value, $argument->getType())) {
            yield $value; return;
        }

        try {
            yield $this->converter->convert($value, $argument->getType());
        } catch (TypeConversionException $e) {
            throw new BadRequestHttpException(sprintf('Cannot convert named argument "%s".', $name), $e);
        }
    }

    abstract protected function getArgumentValue(string $name, Request $request, NamedValueArgument $attribute, ArgumentMetadata $metadata);
}
