<?php

namespace Jungi\FrameworkExtraBundle\Http;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestUtils
{
    public static function getControllerAsCallableSyntax(Request $request): string
    {
        $controller = $request->attributes->get('_controller');
        if (null === $controller) {
            throw new \InvalidArgumentException('Controller attribute is missing.');
        }

        if (is_array($controller)) {
            return ltrim($controller[0], '\\').'::'.$controller[1];
        }

        if (!is_string($controller)) {
            throw new \UnexpectedValueException(sprintf('Expected to get string, got: %s.', gettype($controller)));
        }

        return $controller;
    }
}
