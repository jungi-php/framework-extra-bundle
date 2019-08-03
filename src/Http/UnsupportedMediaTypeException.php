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
     * @param string $mediaType
     *
     * @return static
     */
    public static function mapperNotRegistered(string $mediaType): self
    {
        return new static($mediaType, sprintf('No mapper is registered for media type "%s".', $mediaType));
    }

    /**
     * @param string          $mediaType
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $mediaType, string $message, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->mediaType = $mediaType;
    }

    /**
     * @return string
     */
    public function getMediaType(): string
    {
        return $this->mediaType;
    }
}
