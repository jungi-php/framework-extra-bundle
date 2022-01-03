<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\RequestBody;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Http\ContentDispositionDescriptor;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Utils\TmpFileUtils;
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
    private const DEFAULT_CONTENT_TYPE = 'application/json';

    private $messageBodyMapperManager;
    private $converter;
    private $attributeLocator;
    private $defaultContentType;

    private static $fileClassTypes = [
        UploadedFile::class,
        File::class,
        \SplFileInfo::class,
        \SplFileObject::class,
    ];

    /** @deprecated since v1.4, use constructor instead */
    public static function onAttribute(MessageBodyMapperManager $messageBodyMapperManager, ConverterInterface $converter, ContainerInterface $attributeLocator, string $defaultContentType = self::DEFAULT_CONTENT_TYPE): self
    {
        trigger_deprecation('jungi/framework-extra-bundle', '1.4', 'The "%s" method is deprecated, use the constructor instead.', __METHOD__);

        return new self($messageBodyMapperManager, $converter, $attributeLocator, $defaultContentType);
    }

    /** @deprecated since v1.4, use constructor instead */
    public static function onAnnotation(MessageBodyMapperManager $messageBodyMapperManager, ConverterInterface $converter, ContainerInterface $attributeLocator, string $defaultContentType = self::DEFAULT_CONTENT_TYPE): self
    {
        trigger_deprecation('jungi/framework-extra-bundle', '1.4', 'The "%s" method is deprecated, use the constructor instead.', __METHOD__);

        return new self($messageBodyMapperManager, $converter, $attributeLocator, $defaultContentType);
    }

    public function __construct(MessageBodyMapperManager $messageBodyMapperManager, ConverterInterface $converter, ?ContainerInterface $attributeLocator = null, string $defaultContentType = self::DEFAULT_CONTENT_TYPE)
    {
        $this->messageBodyMapperManager = $messageBodyMapperManager;
        $this->converter = $converter;
        $this->attributeLocator = $attributeLocator;
        $this->defaultContentType = $defaultContentType;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if ($argument->getAttributes(RequestBody::class, ArgumentMetadata::IS_INSTANCEOF)) {
            return true;
        }

        if (null === $this->attributeLocator) {
            return false;
        }

        if (null === $controller = RequestUtils::getControllerAsCallableString($request)) {
            return false;
        }

        $id = $controller.'$'.$argument->getName();

        return $this->attributeLocator->has($id) && $this->attributeLocator->get($id)->has(RequestBody::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$argument->getType()) {
            throw new \InvalidArgumentException(sprintf('Argument "%s" must have the type specified for the request body conversion.', $argument->getName()));
        }

        /** @var RequestBody $attribute */
        $attribute = $argument->getAttributes(RequestBody::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;
        if (null === $attribute) {
            $id = RequestUtils::getControllerAsCallableString($request) . '$' . $argument->getName();
            $attribute = $this->attributeLocator->get($id)->get(RequestBody::class);
        }

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
