<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\RequestBody;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Http\ContentDispositionDescriptor;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Filesystem\TmpFileUtils;
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
    private const DEFAULT_CONTENT_TYPE = 'application/json';

    private MessageBodyMapperManager $messageBodyMapperManager;
    private ConverterInterface $converter;
    private string $defaultContentType;

    private static array $fileClassTypes = [
        UploadedFile::class,
        File::class,
        \SplFileInfo::class,
        \SplFileObject::class,
    ];

    public function __construct(MessageBodyMapperManager $messageBodyMapperManager, ConverterInterface $converter, string $defaultContentType = self::DEFAULT_CONTENT_TYPE)
    {
        $this->messageBodyMapperManager = $messageBodyMapperManager;
        $this->converter = $converter;
        $this->defaultContentType = $defaultContentType;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return (bool) $argument->getAttributes(RequestBody::class, ArgumentMetadata::IS_INSTANCEOF);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$argument->getType()) {
            throw new \InvalidArgumentException(sprintf('Argument "%s" must have the type specified for the request body conversion.', $argument->getName()));
        }

        /** @var RequestBody $attribute */
        $attribute = $argument->getAttributes(RequestBody::class, ArgumentMetadata::IS_INSTANCEOF)[0];
        $argumentType = $attribute->type() ?: $argument->getType();

        // when request parameters are available
        if ($parameters = array_replace_recursive($request->request->all(), $request->files->all())) {
            try {
                yield $this->converter->convert($parameters, $argumentType);
                return;
            } catch (TypeConversionException $e) {
                throw new BadRequestHttpException('Request body parameters are invalid.', $e);
            }
        }

        $contentType = $request->headers->get('CONTENT_TYPE');
        if (null === $contentType && '' !== $request->getContent()) {
            $contentType = $this->defaultContentType;
        }

        // empty body && unavailable content type
        if (null === $contentType) {
            $value = $argument->hasDefaultValue() ? $argument->getDefaultValue() : null;
            if ($value === null && !$argument->isNullable()) {
                throw new BadRequestHttpException('Request body cannot be empty.');
            }

            yield $value;
            return;
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

    private function convertToFile($resource, string $mediaType, string $type, ?string $filename): UploadedFile|File|\SplFileObject|\SplFileInfo
    {
        $tmpFile = TmpFileUtils::fromResource($resource);

        return match ($type) {
            UploadedFile::class => new UploadedFile($tmpFile, $filename ?: '', $mediaType, UPLOAD_ERR_OK, true),
            File::class => new File($tmpFile, false),
            'SplFileObject' => new \SplFileObject($tmpFile),
            'SplFileInfo' => new \SplFileInfo($tmpFile),
            default => throw new \InvalidArgumentException(sprintf('Unknown type "%s".', $type)),
        };
    }
}
