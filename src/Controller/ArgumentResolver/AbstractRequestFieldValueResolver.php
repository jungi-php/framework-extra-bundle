<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestFieldAnnotationInterface;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Util\TypeUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
abstract class AbstractRequestFieldValueResolver implements ArgumentValueResolverInterface
{
    private $annotationClass;
    private $converter;

    public function __construct(string $annotationClass, ConverterInterface $converter)
    {
        if (!is_subclass_of($annotationClass, RequestFieldAnnotationInterface::class)) {
            throw new \InvalidArgumentException(sprintf('Expected a subclass of "%s", got: "%s".', RequestFieldAnnotationInterface::class, $annotationClass));
        }

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

        /** @var RequestFieldAnnotationInterface $annotation */
        $annotation = $annotationRegistry->getArgumentAnnotation($argument->getName(), $this->annotationClass);

        $fieldValue = $this->getFieldValue($request, $annotation->getFieldName(), $argument->getType());

        if (null === $fieldValue && $argument->hasDefaultValue()) {
            $fieldValue = $argument->getDefaultValue();
        }

        if (null === $fieldValue) {
            if ($argument->isNullable()) {
                yield null; return;
            }

            throw new BadRequestHttpException(sprintf('Request field "%s" is not present.', $annotation->getFieldName()));
        }

        if (null === $argument->getType() || TypeUtils::isValueOfType($fieldValue, $argument->getType())) {
            yield $fieldValue; return;
        }

        try {
            yield $this->converter->convert($fieldValue, $argument->getType());
        } catch (TypeConversionException $e) {
            throw new BadRequestHttpException(sprintf('Cannot convert request field "%s".', $annotation->getFieldName()), $e);
        }
    }

    abstract protected function getFieldValue(Request $request, string $name, ?string $type);
}
