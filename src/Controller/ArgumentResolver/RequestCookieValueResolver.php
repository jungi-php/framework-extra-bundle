<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestCookie;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestCookieValueResolver extends AbstractRequestFieldValueResolver
{
    public function __construct(ConverterInterface $converter)
    {
        parent::__construct(RequestCookie::class, $converter);
    }

    public function getFieldValue(Request $request, string $name, ?string $type)
    {
        return $request->cookies->get($name);
    }
}
