<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\NamedValueArgumentInterface;
use Jungi\FrameworkExtraBundle\Annotation\RequestHeader;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestHeaderValueResolver extends AbstractNamedValueArgumentValueResolver
{
    public function __construct(ConverterInterface $converter, ContainerInterface $annotationLocator)
    {
        parent::__construct(RequestHeader::class, $converter, $annotationLocator);
    }

    public function getArgumentValue(Request $request, NamedValueArgumentInterface $annotation, ArgumentMetadata $metadata)
    {
        if ('array' === $metadata->getType()) {
            return $request->headers->all($annotation->getName());
        }

        return $request->headers->get($annotation->getName());
    }
}
