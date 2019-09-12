<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestQueryParam;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestQueryParamValueResolver extends AbstractRequestParamValueResolver
{
    public function __construct(ConverterInterface $converter)
    {
        parent::__construct(RequestQueryParam::class, $converter);
    }

    protected function getParameterBag(Request $request): ParameterBag
    {
        return $request->query;
    }
}
