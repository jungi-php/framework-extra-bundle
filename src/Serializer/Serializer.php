<?php

namespace Jungi\FrameworkExtraBundle\Serializer;

use Symfony\Component\Serializer\Serializer as AbstractSerializer;

if (!defined('Symfony\Component\Serializer\Encoder\XmlEncoder::TYPE_CASE_ATTRIBUTES')) { // removed in v5.0, method signature updated
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
} else {
    /**
     * @author Piotr Kugla <piku235@gmail.com>
     */
    final class Serializer extends AbstractSerializer
    {
        public function denormalize($data, $type, $format = null, array $context = [])
        {
            if ($data instanceof $type) {
                return $data;
            }

            return parent::denormalize($data, $type, $format, $context);
        }

        public function supportsDenormalization($data, $type, $format = null, array $context = [])
        {
            return $data instanceof $type || parent::supportsDenormalization($data, $type, $format, $context);
        }
    }
}
