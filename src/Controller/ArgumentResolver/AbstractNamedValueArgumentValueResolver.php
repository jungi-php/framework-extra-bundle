<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\NamedValueArgumentInterface;
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
 */
abstract class AbstractNamedValueArgumentValueResolver implements ArgumentValueResolverInterface
{
    private $annotationClass;
    private $converter;
    private $annotationLocator;

    public function __construct(string $annotationClass, ConverterInterface $converter, ContainerInterface $annotationLocator)
    {
        if (!is_subclass_of($annotationClass, NamedValueArgumentInterface::class)) {
            throw new \InvalidArgumentException(sprintf('Expected a subclass of "%s", got: "%s".', NamedValueArgumentInterface::class, $annotationClass));
        }

        $this->annotationClass = $annotationClass;
        $this->converter = $converter;
        $this->annotationLocator = $annotationLocator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (null === $controller = RequestUtils::getControllerAsCallableString($request)) {
            return false;
        }

        $id = $controller.'$'.$argument->getName();

        return $this->annotationLocator->has($id) && $this->annotationLocator->get($id)->has($this->annotationClass);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if ($argument->isVariadic()) {
            throw new \InvalidArgumentException('Variadic arguments are not supported.');
        }

        $id = RequestUtils::getControllerAsCallableString($request).'$'.$argument->getName();

        /** @var NamedValueArgumentInterface $annotation */
        $annotation = $this->annotationLocator->get($id)->get($this->annotationClass);

        $value = $this->getArgumentValue($request, $annotation, $argument);

        if (null === $value && $argument->hasDefaultValue()) {
            $value = $argument->getDefaultValue();
        }

        if (null === $value) {
            if ($argument->isNullable()) {
                yield null; return;
            }

            throw new BadRequestHttpException(sprintf('Argument "%s" cannot be found in the request.', $annotation->getName()));
        }

        if (null === $argument->getType() || TypeUtils::isValueOfType($value, $argument->getType())) {
            yield $value; return;
        }

        try {
            yield $this->converter->convert($value, $argument->getType());
        } catch (TypeConversionException $e) {
            throw new BadRequestHttpException(sprintf('Cannot convert named argument "%s".', $annotation->getName()), $e);
        }
    }

    abstract protected function getArgumentValue(Request $request, NamedValueArgumentInterface $annotation, ArgumentMetadata $metadata);
}
