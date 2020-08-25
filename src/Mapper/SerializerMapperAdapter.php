<?php

namespace Jungi\FrameworkExtraBundle\Mapper;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class SerializerMapperAdapter implements MapperInterface
{
    private $format;
    private $context;

    /**
     * @var SerializerInterface|NormalizerInterface|DenormalizerInterface
     */
    private $serializer;

    public function __construct(string $format, SerializerInterface $serializer, array $context = [])
    {
        $this->format = $format;
        $this->context = $context;
        $this->setSerializer($serializer);
    }

    public function mapFrom(string $data, string $type): object
    {
        try {
            return $this->serializer->deserialize($data, $type, $this->format, $this->context);
        } catch (UnexpectedValueException | MissingConstructorArgumentsException | \TypeError $e) {
            if ($e instanceof NotNormalizableValueException
                && !$this->serializer->supportsDenormalization($data, $type, $this->format)
            ) {
                throw new \InvalidArgumentException(sprintf('Cannot deserialize data to type "%s".', $type), 0, $e);
            }

            throw new MalformedDataException(sprintf('Data is malformed: %s', $e->getMessage()), 0, $e);
        }
    }

    public function mapTo($data): string
    {
        try {
            return $this->serializer->serialize($data, $this->format, $this->context);
        } catch (NotNormalizableValueException $e) {
            throw new \InvalidArgumentException(sprintf('Cannot serialize data to format "%s".', $this->format), 0, $e);
        }
    }

    private function setSerializer(SerializerInterface $serializer): void
    {
        if (!$serializer instanceof NormalizerInterface || !$serializer instanceof DenormalizerInterface
            || !$serializer instanceof EncoderInterface || !$serializer instanceof DecoderInterface
        ) {
            throw new \InvalidArgumentException('Expected a serializer that also implements NormalizerInterface, DenormalizerInterface, EncoderInterface and DecoderInterface.');
        }

        if (!$serializer->supportsEncoding($this->format) || !$serializer->supportsDecoding($this->format)) {
            throw new \InvalidArgumentException(sprintf('Format "%s" is not fully supported by serializer.', $this->format));
        }

        $this->serializer = $serializer;
    }
}
