<?php

namespace Jungi\FrameworkExtraBundle\Serializer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class ObjectAlreadyOfTypeDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (!is_object($data)) {
            throw new InvalidArgumentException(sprintf('Data must be of object type, given "%s".', get_debug_type($data)));
        }
        if ($type !== get_class($data)) {
            throw new InvalidArgumentException(sprintf('Data expected to be of type "%s", given "%s".', $type, get_debug_type($data)));
        }

        return $data;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return is_object($data) && $type === get_class($data);
    }
}