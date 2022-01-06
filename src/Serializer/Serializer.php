<?php

namespace Jungi\FrameworkExtraBundle\Serializer;

use Symfony\Component\Serializer\Serializer as AbstractSerializer;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class Serializer extends AbstractSerializer
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if ($data instanceof $type) {
            return $data;
        }

        return parent::denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return $data instanceof $type || parent::supportsDenormalization($data, $type, $format, $context);
    }
}