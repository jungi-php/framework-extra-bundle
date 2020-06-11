<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Util\TmpFileUtils;
use Jungi\FrameworkExtraBundle\Http\ContentDispositionDescriptor;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Util\TypeUtils;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    private static $fileClassTypes = [
        UploadedFile::class,
        File::class,
        \SplFileInfo::class,
        \SplFileObject::class,
    ];

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

        $annotationRegistry = RequestUtils::getControllerAnnotationRegistry($request);
        /** @var RequestBody $annotation */
        $annotation = $annotationRegistry->getArgumentAnnotation($argument->getName(), RequestBody::class);

        if (null !== $annotation->getArgumentType()) {
            if ('array' !== $argument->getType()) {
                throw new \InvalidArgumentException(sprintf(
                    'Expected argument "%s" to be type hinted as "array", got "%s", the annotation indicates "%s".',
                    $argument->getName(),
                    $argument->getType(),
                    $annotation->getArgumentType()
                ));
            }

            $argumentType = $annotation->getArgumentType();
            $verifyType = TypeUtils::getCollectionBaseElementType($annotation->getArgumentType());
        } else {
            $argumentType = $verifyType = $argument->getType();
        }

        // when request parameters are available
        if ($parameters = array_replace($request->files->all(), $request->request->all())) {
            try {
                yield $this->converter->convert($parameters, $argumentType); return;
            } catch (TypeConversionException $e) {
                throw new BadRequestHttpException('Request body parameters are invalid.', $e);
            }
        }

        // when argument type is not of class type
        if (!class_exists($verifyType)) {
            try {
                yield $this->converter->convert($request->getContent(), $argumentType); return;
            } catch (TypeConversionException $e) {
                throw new BadRequestHttpException('Request body is malformed.', $e);
            }
        }

        $contentType = $request->headers->get('CONTENT_TYPE');

        if (null === $contentType) {
            throw new BadRequestHttpException('The request content type must be specified.');
        }

        // file as the request body
        if (in_array($argumentType, self::$fileClassTypes, true)) {
            $filename = null;

            if ($headerValue = $request->headers->get('CONTENT_DISPOSITION')) {
                $contentDisposition = ContentDispositionDescriptor::parse($headerValue);
                $filename = $contentDisposition->isInline() ? $contentDisposition->getFilename() : null;
            }

            yield $this->convertToFile($request->getContent(true), $contentType, $argumentType, $filename);
            return;
        }

        try {
            yield $this->messageBodyMapperManager->mapFrom($request->getContent(), $contentType, $argumentType);
        } catch (MalformedDataException $e) {
            throw new BadRequestHttpException('Request body is malformed.', $e);
        }
    }

    private function convertToFile($resource, string $mediaType, string $type, ?string $filename): \SplFileInfo
    {
        $tmpFile = TmpFileUtils::fromResource($resource);

        switch ($type) {
            case UploadedFile::class:
                return new UploadedFile($tmpFile, $filename ?: '', $mediaType, UPLOAD_ERR_OK, true);
            case File::class:
                return new File($tmpFile, false);
            case 'SplFileObject':
                return new \SplFileObject($tmpFile);
            case 'SplFileInfo':
                return new \SplFileInfo($tmpFile);
            default:
                throw new \InvalidArgumentException(sprintf('Unknown type "%s".', $type));
        }
    }
}
