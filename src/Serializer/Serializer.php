<?php

namespace Jungi\FrameworkExtraBundle\Serializer;

use Symfony\Component\Serializer\Serializer as AbstractSerializer;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class Serializer extends AbstractSerializer
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if ($data instanceof $type) {
            return $data;
        }

        return parent::denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return $data instanceof $type || parent::supportsDenormalization($data, $type, $format, $context);
    }
}
