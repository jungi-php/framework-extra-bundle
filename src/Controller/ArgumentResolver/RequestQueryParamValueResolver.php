<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestQueryParam;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestQueryParamValueResolver extends AbstractRequestFieldValueResolver
{
    public function __construct(ConverterInterface $converter)
    {
        parent::__construct(RequestQueryParam::class, $converter);
    }

    protected function getFieldValue(Request $request, string $name, ?string $type)
    {
        return $request->query->get($name);
    }
}
