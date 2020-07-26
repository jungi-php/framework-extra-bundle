<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestHeader;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestHeaderValueResolver extends AbstractNamedValueArgumentValueResolver
{
    public function __construct(ConverterInterface $converter, ContainerInterface $annotationLocator)
    {
        parent::__construct(RequestHeader::class, $converter, $annotationLocator);
    }

    public function getArgumentValue(Request $request, string $name, ?string $type)
    {
        if ('array' === $type) {
            return $request->headers->all($name);
        }

        return $request->headers->get($name);
    }
}
