<?php

namespace Jungi\FrameworkExtraBundle\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Represents an HTTP response with an entity that is mapped to
 * the selected content type using the content negotiation.
 *
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @final
 */
class EntityResponse extends Response
{
    private const MEDIA_TYPE_ALL = '*/*';

    private mixed $entity;

    public function __construct(mixed $entity, int $status = 200, array $headers = [])
    {
        parent::__construct('', $status, $headers);

        $this->setEntity($entity);
    }

    /**
     * Sets the entity that is returned in the response.
     */
    public function setEntity(mixed $entity): static
    {
        $this->entity = $entity;
        $this->setContent('');

        return $this;
    }

    /**
     * Returns the entity that is returned in the response.
     */
    public function getEntity(): mixed
    {
        return $this->entity;
    }

    /**
     * Executed later.
     *
     * @see EntityResponse::negotiateContent()
     */
    public function prepare(Request $request): static
    {
        return $this;
    }

    /**
     * Negotiates the response content.
     *
     * The entity is mapped to the selected content type using
     * the content negotiation.
     */
    public function negotiateContent(Request $request, MessageBodyMapperManager $messageBodyMapperManager, string $defaultContentType): static
    {
        $defaultContentType = MediaTypeDescriptor::parse($defaultContentType);
        $acceptableMediaTypes = $this->resolveAcceptableMediaTypes($request) ?: [$defaultContentType];
        $supportedMediaTypes = MediaTypeDescriptor::parseList($messageBodyMapperManager->getSupportedMediaTypes());

        if (!$supportedMediaTypes) {
            throw new \LogicException('You need to register at least one message body mapper for an entity response. For a JSON content type, you can use the built-in message body mapper by running "composer require symfony/serializer".');
        }

        $contentType = $this->selectResponseContentType($acceptableMediaTypes, $supportedMediaTypes);
        if (!$contentType) {
            throw new NotAcceptableMediaTypeException(MediaTypeDescriptor::listToString($acceptableMediaTypes), MediaTypeDescriptor::listToString($supportedMediaTypes), 'Could not select any content type for response.');
        }

        $this->headers->set('Content-Type', $contentType->toString());
        $this->setContent($messageBodyMapperManager->mapTo($this->entity, $contentType->toString()));

        parent::prepare($request);

        return $this;
    }

    /**
     * @param MediaTypeDescriptor[] $acceptableMediaTypes
     * @param MediaTypeDescriptor[] $supportedMediaTypes
     */
    private function selectResponseContentType(array $acceptableMediaTypes, array $supportedMediaTypes): ?MediaTypeDescriptor
    {
        foreach ($acceptableMediaTypes as $acceptableMediaType) {
            foreach ($supportedMediaTypes as $supportedMediaType) {
                if ($acceptableMediaType->inRange($supportedMediaType)) {
                    return $supportedMediaType;
                }
            }
        }

        return null;
    }

    /**
     * Resolves from:
     *  1. Request format
     *  2. Accept header
     *
     * @return MediaTypeDescriptor[]
     */
    private function resolveAcceptableMediaTypes(Request $request): array
    {
        $format = $request->getRequestFormat(null);
        $mediaType = null !== $format ? $request->getMimeType($format) : null;

        if (null !== $format && null !== $mediaType && null !== $descriptor = MediaTypeDescriptor::parseOrNull($mediaType)) {
            return [$descriptor];
        }

        if ($acceptableContentTypes = $request->getAcceptableContentTypes()) {
            // acceptable content types are already sorted
            $descriptors = [];
            foreach ($acceptableContentTypes as $contentType) {
                // [ignored] Accept: */*
                if (self::MEDIA_TYPE_ALL !== $contentType && null !== $descriptor = MediaTypeDescriptor::parseOrNull($contentType)) {
                    $descriptors[] = $descriptor;
                }
            }

            if ($descriptors) {
                return $descriptors;
            }
        }

        return [];
    }
}