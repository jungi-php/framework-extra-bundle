<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestParamValueResolver extends AbstractRequestFieldValueResolver
{
    public function __construct(ConverterInterface $converter, ContainerInterface $annotationLocator)
    {
        parent::__construct(RequestParam::class, $converter, $annotationLocator);
    }

    public function getFieldValue(Request $request, string $name, ?string $type)
    {
        if ($this !== $result = $request->files->get($name, $this)) {
            return $result;
        }

        return $request->request->get($name);
    }
}
