<?php

namespace Jungi\FrameworkExtraBundle\Http;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
final class MediaTypeUtils
{
    public static function isSpecific(string $mediaType): bool
    {
        $descriptor = MediaTypeDescriptor::parseOrNull($mediaType);

        return null !== $descriptor && $descriptor->isSpecific();
    }
}
