<?php

namespace Jungi\FrameworkExtraBundle\Http;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestUtils
{
    public static function getControllerAsCallableString(Request $request): ?string
    {
        $controller = $request->attributes->get('_controller');
        if (null === $controller) {
            throw new \InvalidArgumentException('Attribute "_controller" cannot be found in the request.');
        }

        if (is_array($controller) && is_callable($controller, true) && is_string($controller[0])) {
            $controller = $controller[0].'::'.$controller[1];
        }

        return is_string($controller) ? ltrim($controller, '\\') : null;
    }
}
