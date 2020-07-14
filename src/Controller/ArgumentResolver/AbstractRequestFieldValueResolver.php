<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestFieldAnnotationInterface;
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
abstract class AbstractRequestFieldValueResolver implements ArgumentValueResolverInterface
{
    private $annotationClass;
    private $converter;
    private $annotationLocator;

    public function __construct(string $annotationClass, ConverterInterface $converter, ContainerInterface $annotationLocator)
    {
        if (!is_subclass_of($annotationClass, RequestFieldAnnotationInterface::class)) {
            throw new \InvalidArgumentException(sprintf('Expected a subclass of "%s", got: "%s".', RequestFieldAnnotationInterface::class, $annotationClass));
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

        /** @var RequestFieldAnnotationInterface $annotation */
        $annotation = $this->annotationLocator->get($id)->get($this->annotationClass);

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
