<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class QueryParamValueResolver extends AbstractNamedValueArgumentValueResolver
{
    public function __construct(ConverterInterface $converter, ContainerInterface $annotationLocator)
    {
        parent::__construct(QueryParam::class, $converter, $annotationLocator);
    }

    protected function getArgumentValue(Request $request, string $name, ?string $type)
    {
        return $request->query->get($name);
    }
}
