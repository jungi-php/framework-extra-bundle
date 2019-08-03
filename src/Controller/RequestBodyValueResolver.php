<?php

namespace Jungi\FrameworkExtraBundle\Controller;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Http\Conversion\MessageBodyConversionManager;
use Jungi\FrameworkExtraBundle\Http\UnsupportedMediaTypeException;
use Jungi\FrameworkExtraBundle\RequestAttributes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestBodyValueResolver implements ArgumentValueResolverInterface
{
    private $messageBodyConversionManager;

    public function __construct(MessageBodyConversionManager $messageBodyConversionManager)
    {
        $this->messageBodyConversionManager = $messageBodyConversionManager;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        /** @var RequestBody|null $annotation */
        $annotation = $request->attributes->get(RequestAttributes::REQUEST_BODY_CONVERSION);

        return $annotation && $annotation->getName() === $argument->getName();
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if (!class_exists($argument->getType())) {
            throw new \InvalidArgumentException(sprintf(
                'Argument "%s" has invalid type "%s" for request body conversion.',
                $argument->getName(),
                $argument->getType()
            ));
        }

        $contentType = $request->headers->get('CONTENT_TYPE');
        if (null === $contentType) {
            throw new BadRequestHttpException('The request content type must be specified.');
        }

        try {
            yield $this->messageBodyConversionManager->convertFromInputMessage(
                $request->getContent(),
                $argument->getType(),
                $contentType
            );
        } catch (UnsupportedMediaTypeException $e) {
            throw new UnsupportedMediaTypeHttpException(sprintf(
                'Content type "%s" is not supported.',
                $e->getMediaType()
            ), $e);
        }
    }
}
