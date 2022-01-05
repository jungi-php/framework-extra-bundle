<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\RequestParam;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestParamValueResolver extends AbstractNamedValueArgumentValueResolver
{
    protected static $attributeClass = RequestParam::class;

    protected function getArgumentValue(NamedValueArgument $argument, Request $request)
    {
        if ($this !== $result = $request->files->get($argument->getName(), $this)) {
            return $result;
        }

        return $request->request->get($argument->getName());
    }
}
