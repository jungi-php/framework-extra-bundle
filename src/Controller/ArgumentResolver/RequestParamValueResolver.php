<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\NamedValueArgumentInterface;
use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestParamValueResolver extends AbstractNamedValueArgumentValueResolver
{
    public function __construct(ConverterInterface $converter, ContainerInterface $annotationLocator)
    {
        parent::__construct(RequestParam::class, $converter, $annotationLocator);
    }

    public function getArgumentValue(Request $request, NamedValueArgumentInterface $annotation, ArgumentMetadata $metadata)
    {
        if ($this !== $result = $request->files->get($annotation->getName(), $this)) {
            return $result;
        }

        return $request->request->get($annotation->getName());
    }
}
