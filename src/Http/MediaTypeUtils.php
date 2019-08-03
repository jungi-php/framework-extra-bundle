<?php

namespace Jungi\FrameworkExtraBundle\Http;

/**
 * @internal
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class MediaTypeUtils
{
    public static function isSpecific(string $mediaType): bool
    {
        $descriptor = MediaTypeDescriptor::parseOrNull($mediaType);

        return null !== $descriptor && $descriptor->isSpecific();
    }
}
