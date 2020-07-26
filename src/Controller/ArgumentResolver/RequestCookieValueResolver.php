<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestCookie;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestCookieValueResolver extends AbstractNamedValueArgumentValueResolver
{
    public function __construct(ConverterInterface $converter, ContainerInterface $annotationLocator)
    {
        parent::__construct(RequestCookie::class, $converter, $annotationLocator);
    }

    public function getArgumentValue(Request $request, string $name, ?string $type)
    {
        return $request->cookies->get($name);
    }
}
