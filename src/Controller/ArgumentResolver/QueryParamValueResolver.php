<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\QueryParam;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class QueryParamValueResolver extends AbstractNamedValueArgumentValueResolver
{
    protected static $attributeClass = QueryParam::class;

    protected function getArgumentValue(NamedValueArgument $argument, Request $request)
    {
        return $request->query->get($argument->getName());
    }
}
