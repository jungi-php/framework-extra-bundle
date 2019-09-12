<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Annotation\RequestQueryParam;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestQueryParamValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestQueryParamValueResolverTest extends AbstractRequestParamValueResolverTest
{
    protected function createArgumentValueResolver(ConverterInterface $converter): ArgumentValueResolverInterface
    {
        return new RequestQueryParamValueResolver($converter);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        return new Request($parameters);
    }

    protected function createAnnotation(string $name): RequestParam
    {
        return new RequestQueryParam(array('value' => $name));
    }
}
