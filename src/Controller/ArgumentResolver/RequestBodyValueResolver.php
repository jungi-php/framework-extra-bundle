<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Http\ContentDispositionDescriptor;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Utils\TmpFileUtils;
use Jungi\FrameworkExtraBundle\Utils\TypeUtils;
use Psr\Container\ContainerInterface;
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
    private $annotationLocator;
    private $defaultContentType;

    private static $fileClassTypes = [
        UploadedFile::class,
        File::class,
        \SplFileInfo::class,
        \SplFileObject::class,
    ];

    public function __construct(MessageBodyMapperManager $messageBodyMapperManager, ConverterInterface $converter, ContainerInterface $annotationLocator, string $defaultContentType = 'text/plain')
    {
        $this->messageBodyMapperManager = $messageBodyMapperManager;
        $this->converter = $converter;
        $this->annotationLocator = $annotationLocator;
        $this->defaultContentType = $defaultContentType;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (null === $controller = RequestUtils::getControllerAsCallableString($request)) {
            return false;
        }

        $id = $controller.'$'.$argument->getName();

        return $this->annotationLocator->has($id) && $this->annotationLocator->get($id)->has(RequestBody::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if (!$argument->getType()) {
            throw new \InvalidArgumentException(sprintf('Argument "%s" must have the type specified for the request body conversion.', $argument->getName()));
        }
        if ($argument->isNullable()) {
            throw new \InvalidArgumentException(sprintf('Argument "%s" cannot be nullable for the request body conversion.', $argument->getName()));
        }

        $contentType = $request->headers->get('CONTENT_TYPE') ?: $this->defaultContentType;

        $id = RequestUtils::getControllerAsCallableString($request).'$'.$argument->getName();
        /** @var RequestBody $annotation */
        $annotation = $this->annotationLocator->get($id)->get(RequestBody::class);

        if (null !== $annotation->type()) {
            if ('array' !== $argument->getType()) {
                throw new \InvalidArgumentException(sprintf('Expected argument "%s" to be type hinted as "array", got "%s", the annotation indicates "%s".', $argument->getName(), $argument->getType(), $annotation->type()));
            }

            if (!TypeUtils::isCollection($annotation->type())) {
                throw new \InvalidArgumentException(sprintf('Expected a collection type, got "%s".', $annotation->type()));
            }

            $argumentType = $annotation->type();
        } else {
            $argumentType = $argument->getType();
        }

        // when request parameters are available
        if ($parameters = array_replace_recursive($request->request->all(), $request->files->all())) {
            try {
                yield $this->converter->convert($parameters, $argumentType); return;
            } catch (TypeConversionException $e) {
                throw new BadRequestHttpException('Request body parameters are invalid.', $e);
            }
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
