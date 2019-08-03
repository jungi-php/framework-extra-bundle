<?php

namespace Jungi\FrameworkExtraBundle;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestAttributes
{
    const REQUEST_BODY_CONVERSION = '_request_body_conversion';
    const RESPONSE_BODY_CONVERSION = '_response_body_conversion';

    private function __construct()
    {
    }
}
