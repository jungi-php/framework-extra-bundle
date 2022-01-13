<?php

namespace Jungi\FrameworkExtraBundle\Http;

use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Mapper\MapperInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @final
 */
class MessageBodyMapperManager
{
    private ServiceProviderInterface $mappers;

    public function __construct(ServiceProviderInterface $mappers)
    {
        $this->mappers = $mappers;
    }

    /**
     * @return string[]
     */
    public function getSupportedMediaTypes(): array
    {
        return array_keys($this->mappers->getProvidedServices());
    }

    /**
     * @throws UnsupportedMediaTypeException
     * @throws MalformedDataException
     */
    public function mapFrom(string $messageBody, string $mediaType, string $type): mixed
    {
        if (!$this->mappers->has($mediaType)) {
            throw UnsupportedMediaTypeException::mapperNotRegistered($mediaType);
        }

        /** @var MapperInterface $mapper */
        $mapper = $this->mappers->get($mediaType);

        return $mapper->mapFrom($messageBody, $type);
    }

    /**
     * @throws UnsupportedMediaTypeException
     */
    public function mapTo(mixed $data, string $mediaType): string
    {
        if (!$this->mappers->has($mediaType)) {
            throw UnsupportedMediaTypeException::mapperNotRegistered($mediaType);
        }

        /** @var MapperInterface $mapper */
        $mapper = $this->mappers->get($mediaType);

        return $mapper->mapTo($data);
    }
}
