<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class QueryParamValueResolver extends AbstractRequestFieldValueResolver
{
    public function __construct(ConverterInterface $converter)
    {
        parent::__construct(QueryParam::class, $converter);
    }

    protected function getFieldValue(Request $request, string $name, ?string $type)
    {
        return $request->query->get($name);
    }
}
