<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestBodyValueResolver implements ArgumentValueResolverInterface
{
    private $messageBodyMapperManager;
    private $converter;

    public function __construct(MessageBodyMapperManager $messageBodyMapperManager, ConverterInterface $converter)
    {
        $this->messageBodyMapperManager = $messageBodyMapperManager;
        $this->converter = $converter;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        $annotationRegistry = RequestUtils::getControllerAnnotationRegistry($request);

        return $annotationRegistry && $annotationRegistry->hasArgumentAnnotation($argument->getName(), RequestBody::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if (!$argument->getType()) {
            throw new \InvalidArgumentException(sprintf(
                'Argument "%s" must have the type specified for the request body conversion.',
                $argument->getName()
            ));
        }
        if ($argument->isNullable()) {
            throw new \InvalidArgumentException(sprintf(
                'Argument "%s" cannot be nullable for the request body conversion.',
                $argument->getName()
            ));
        }

        // when request parameters are available
        if ($parameters = RequestUtils::getRequestBodyParameters($request)) {
            try {
                yield $this->converter->convert($parameters, $argument->getType()); return;
            } catch (TypeConversionException $e) {
                throw new BadRequestHttpException('Request body parameters are invalid.', $e);
            }
        }

        // when argument type is not of class type
        if (!class_exists($argument->getType())) {
            try {
                yield $this->converter->convert($request->getContent(), $argument->getType()); return;
            } catch (TypeConversionException $e) {
                throw new BadRequestHttpException('Request body is malformed.', $e);
            }
        }

        $contentType = $request->headers->get('CONTENT_TYPE');

        if (null === $contentType) {
            throw new BadRequestHttpException('The request content type must be specified.');
        }

        try {
            yield $this->messageBodyMapperManager->mapFrom($request->getContent(), $contentType, $argument->getType());
        } catch (MalformedDataException $e) {
            throw new BadRequestHttpException('Request body is malformed.', $e);
        }
    }
}
