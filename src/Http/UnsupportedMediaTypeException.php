<?php

namespace Jungi\FrameworkExtraBundle\Http;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class UnsupportedMediaTypeException extends \RuntimeException
{
    /**
     * @var string
     */
    protected $mediaType;

    /**
     * @return static
     */
    public static function mapperNotRegistered(string $mediaType): self
    {
        return new static($mediaType, sprintf('No mapper is registered for media type "%s".', $mediaType));
    }

    /**
     * @param int $code
     */
    public function __construct(string $mediaType, string $message, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->mediaType = $mediaType;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }
}
