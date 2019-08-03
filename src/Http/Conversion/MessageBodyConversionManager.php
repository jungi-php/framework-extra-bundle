<?php

namespace Jungi\FrameworkExtraBundle\Http\Conversion;

use Jungi\FrameworkExtraBundle\Http\Conversion\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Http\Conversion\Mapper\MapperInterface;
use Jungi\FrameworkExtraBundle\Http\UnsupportedMediaTypeException;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @final
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
class MessageBodyConversionManager
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
     * @param string inputMessage
     * @param string $type
     * @param string $mediaType
     *
     * @return object
     *
     * @throws UnsupportedMediaTypeException
     * @throws MalformedDataException
     */
    public function convertFromInputMessage(string $inputMessage, string $type, string $mediaType): object
    {
        if (!$this->mappers->has($mediaType)) {
            throw UnsupportedMediaTypeException::mapperNotRegistered($mediaType);
        }

        /** @var MapperInterface $mapper */
        $mapper = $this->mappers->get($mediaType);

        return $mapper->mapFromData($inputMessage, $type);
    }

    /**
     * @param mixed  $data
     * @param string $mediaType
     *
     * @return string
     *
     * @throws UnsupportedMediaTypeException
     */
    public function convertToOutputMessage($data, string $mediaType): string
    {
        if (!$this->mappers->has($mediaType)) {
            throw UnsupportedMediaTypeException::mapperNotRegistered($mediaType);
        }

        /** @var MapperInterface $mapper */
        $mapper = $this->mappers->get($mediaType);

        return $mapper->mapDataTo($data);
    }

    /**
     * @return string[]
     */
    public function getSupportedMediaTypes(): array
    {
        return array_keys($this->mappers->getProvidedServices());
    }
}
