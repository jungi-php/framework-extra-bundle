<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestQuery;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestQueryValueResolver implements ArgumentValueResolverInterface
{
    private $converter;

    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        $annotationRegistry = RequestUtils::getControllerAnnotationRegistry($request);

        return $annotationRegistry && $annotationRegistry->hasArgumentAnnotation($argument->getName(), RequestQuery::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if (!$argument->getType()) {
            throw new \InvalidArgumentException(sprintf(
                'Argument "%s" must have the type specified for the request query conversion.',
                $argument->getName()
            ));
        }
        if ($argument->isNullable()) {
            throw new \InvalidArgumentException(sprintf(
                'Argument "%s" cannot be nullable for the request query conversion.',
                $argument->getName()
            ));
        }
        if (!class_exists($argument->getType())) {
            throw new \InvalidArgumentException(sprintf(
                'Argument "%s" must be of concrete class type for the request query conversion.',
                $argument->getName()
            ));
        }

        yield $this->converter->convert($request->query->all(), $argument->getType());
    }
}
