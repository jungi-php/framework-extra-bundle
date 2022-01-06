<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\QueryParam;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\QueryParamValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class QueryParamValueResolverTest extends AbstractNamedValueArgumentValueResolverTest
{
    protected function createArgumentValueResolver(ConverterInterface $converter): ArgumentValueResolverInterface
    {
        return new QueryParamValueResolver($converter);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        return new Request($parameters);
    }

    protected function createAttribute(string $name): NamedValue
    {
        return new QueryParam($name);
    }
}
