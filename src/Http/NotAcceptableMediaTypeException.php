<?php

namespace Jungi\FrameworkExtraBundle\Http;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class NotAcceptableMediaTypeException extends \RuntimeException
{
    /**
     * @var string[]
     */
    protected $notAcceptableMediaTypes;

    /**
     * @var string[]
     */
    protected $supportedMediaTypes;

    /**
     * @param string[] $notAcceptableMediaTypes
     * @param string[] $supportedMediaTypes
     * @param int      $code
     */
    public function __construct(array $notAcceptableMediaTypes, array $supportedMediaTypes, string $message, $code = 0, \Throwable $previous = null)
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
