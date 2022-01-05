<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\RequestCookie;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestCookieValueResolver extends AbstractNamedValueArgumentValueResolver
{
    protected static $attributeClass = RequestCookie::class;

    protected function getArgumentValue(NamedValueArgument $argument, Request $request)
    {
        return $request->cookies->get($argument->getName());
    }
}
