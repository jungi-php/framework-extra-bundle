<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Util\TypeUtils;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
abstract class AbstractRequestParamValueResolver implements ArgumentValueResolverInterface
{
    private $annotationClass;
    private $converter;

    public function __construct(string $annotationClass, ConverterInterface $converter)
    {
        $this->annotationClass = $annotationClass;
        $this->converter = $converter;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (null === $annotationRegistry = RequestUtils::getControllerAnnotationRegistry($request)) {
            return false;
        }

        return $annotationRegistry->hasArgumentAnnotation($argument->getName(), $this->annotationClass);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if ($argument->isVariadic()) {
            throw new \InvalidArgumentException('Variadic arguments are not supported.');
        }

        $annotationRegistry = RequestUtils::getControllerAnnotationRegistry($request);
        $annotation = $annotationRegistry->getArgumentAnnotation($argument->getName(), $this->annotationClass);
        $parameterBag = $this->getParameterBag($request);

        $field = $annotation->getField() ?: $annotation->getName();
        $paramValue = $parameterBag->get($field, $argument->hasDefaultValue() ? $argument->getDefaultValue() : null);

        if (null === $paramValue) {
            if ($argument->isNullable()) {
                yield null; return;
            }

            throw new BadRequestHttpException(sprintf('Request parameter "%s" is not present.', $field));
        }

        if (null === $argument->getType() || TypeUtils::isValueOfType($paramValue, $argument->getType())) {
            yield $paramValue; return;
        }

        try {
            yield $this->converter->convert($paramValue, $argument->getType()); return;
        } catch (TypeConversionException $e) {
            throw new BadRequestHttpException(sprintf('Invalid request parameter "%s".', $field), $e);
        }
    }

    abstract protected function getParameterBag(Request $request): ParameterBag;
}
