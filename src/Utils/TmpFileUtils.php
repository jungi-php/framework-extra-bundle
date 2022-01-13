<?php

namespace Jungi\FrameworkExtraBundle\Utils;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
final class TmpFileUtils
{
    private static array $resources = [];

    public static function fromData(string $data, string $mimeType = 'application/octet-stream'): string
    {
        $resource = @fopen(sprintf('data:%s;base64,%s', $mimeType, base64_encode($data)), 'r');
        if (!$resource) {
            throw new \RuntimeException(error_get_last()['message']);
        }

        return self::fromResource($resource);
    }

    public static function fromResource($resource): string
    {
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException('Expected to get a resource.');
        }

        $tmpResource = tmpfile();
        $tmpFilename = stream_get_meta_data($tmpResource)['uri'];
        self::$resources[] = $tmpResource;

        stream_copy_to_stream($resource, $tmpResource);

        return $tmpFilename;
    }

    public static function removeReferences(): void
    {
        self::$resources = [];
    }
}
