<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestParamValueResolver extends AbstractRequestFieldValueResolver
{
    public function __construct(ConverterInterface $converter)
    {
        parent::__construct(RequestParam::class, $converter);
    }

    public function getFieldValue(Request $request, string $name, ?string $type)
    {
        if ($this !== $result = $request->files->get($name, $this)) {
            return $result;
        }

        return $request->request->get($name);
    }
}
