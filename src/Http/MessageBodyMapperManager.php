<?php

namespace Jungi\FrameworkExtraBundle\Http;

use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Mapper\MapperInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @final
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class MessageBodyMapperManager
{
    /**
     * @var ServiceProviderInterface
     */
    private $mappers;

    /**
     * @param ServiceProviderInterface $mappers
     */
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
     * @param string $messageBody
     * @param string $mediaType
     * @param string $type
     *
     * @return object
     *
     * @throws UnsupportedMediaTypeException
     * @throws MalformedDataException
     */
    public function mapFrom(string $messageBody, string $mediaType, string $type): object
    {
        if (!$this->mappers->has($mediaType)) {
            throw UnsupportedMediaTypeException::mapperNotRegistered($mediaType);
        }

        /** @var MapperInterface $mapper */
        $mapper = $this->mappers->get($mediaType);

        return $mapper->mapFrom($messageBody, $type);
    }

    /**
     * @param mixed  $data
     * @param string $mediaType
     *
     * @return string
     *
     * @throws UnsupportedMediaTypeException
     */
    public function mapTo($data, string $mediaType): string
    {
        if (!$this->mappers->has($mediaType)) {
            throw UnsupportedMediaTypeException::mapperNotRegistered($mediaType);
        }

        /** @var MapperInterface $mapper */
        $mapper = $this->mappers->get($mediaType);

        return $mapper->mapTo($data);
    }
}
