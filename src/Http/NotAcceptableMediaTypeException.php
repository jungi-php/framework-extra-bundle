<?php

namespace Jungi\FrameworkExtraBundle\Http;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class NotAcceptableMediaTypeException extends \RuntimeException
{
    /** @var string[] */
    protected array $notAcceptableMediaTypes;

    /** @var string[] */
    protected array $supportedMediaTypes;

    /**
     * @param string[] $notAcceptableMediaTypes
     * @param string[] $supportedMediaTypes
     */
    public function __construct(array $notAcceptableMediaTypes, array $supportedMediaTypes, string $message, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->notAcceptableMediaTypes = $notAcceptableMediaTypes;
        $this->supportedMediaTypes = $supportedMediaTypes;
    }

    /**
     * @return string[]
     */
    public function getNotAcceptableMediaTypes(): array
    {
        return $this->notAcceptableMediaTypes;
    }

    /**
     * @return string[]
     */
    public function getSupportedMediaTypes(): array
    {
        return $this->supportedMediaTypes;
    }
}
