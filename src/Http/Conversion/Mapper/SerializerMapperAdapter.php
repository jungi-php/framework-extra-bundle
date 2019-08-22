<?php

namespace Jungi\FrameworkExtraBundle\Http\Conversion\Mapper;

use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Serializer;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class SerializerMapperAdapter implements MapperInterface
{
    private $format;
    private $serializer;

    public function __construct(string $format, Serializer $serializer)
    {
        if (!$serializer->supportsEncoding($format) || !$serializer->supportsDecoding($format)) {
            throw new \InvalidArgumentException(sprintf('Format "%s" is not fully supported by serializer.', $format));
        }

        $this->format = $format;
        $this->serializer = $serializer;
    }

    public function mapFromData(string $data, string $type): object
    {
        try {
            return $this->serializer->deserialize($data, $type, $this->format);
        } catch (UnexpectedValueException | MissingConstructorArgumentsException $e) {
            if ($e instanceof NotNormalizableValueException
                && !$this->serializer->supportsDenormalization($data, $type, $this->format)
            ) {
                throw new \InvalidArgumentException(sprintf('Cannot deserialize data to type "%s".', $type), 0, $e);
            }

            throw new MalformedDataException(sprintf('Data is malformed: %s', $e->getMessage()), 0, $e);
        }
    }

    public function mapDataTo($data): string
    {
        try {
            return $this->serializer->serialize($data, $this->format);
        } catch (NotNormalizableValueException $e) {
            throw new \InvalidArgumentException(sprintf('Cannot serialize data to format "%s".', $this->format), 0, $e);
        }
    }
}
