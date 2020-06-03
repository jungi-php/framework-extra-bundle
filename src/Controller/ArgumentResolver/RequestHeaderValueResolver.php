<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestHeader;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestHeaderValueResolver extends AbstractRequestFieldValueResolver
{
    public function __construct(ConverterInterface $converter)
    {
        parent::__construct(RequestHeader::class, $converter);
    }

    public function getFieldValue(Request $request, string $name, ?string $type)
    {
        if ('array' === $type) {
            return $request->headers->all($name);
        }

        return $request->headers->get($name);
    }
}
