<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestBodyParam;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestBodyParamValueResolver extends AbstractRequestParamValueResolver
{
    public function __construct(ConverterInterface $converter)
    {
        parent::__construct(RequestBodyParam::class, $converter);
    }

    public function getParameterBag(Request $request): ParameterBag
    {
        return new ParameterBag(RequestUtils::getRequestBodyParameters($request));
    }
}
