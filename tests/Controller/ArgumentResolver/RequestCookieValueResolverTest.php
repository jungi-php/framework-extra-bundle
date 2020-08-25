<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestCookie;
use Jungi\FrameworkExtraBundle\Annotation\NamedValueArgument;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestCookieValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestCookieValueResolverTest extends AbstractNamedValueArgumentValueResolverTest
{
    public function argumentTypeSameAsParameterType()
    {
        $this->markTestSkipped('always as string value');
    }

    protected function createArgumentValueResolver(ConverterInterface $converter, ContainerInterface $annotationLocator): ArgumentValueResolverInterface
    {
        return new RequestCookieValueResolver($converter, $annotationLocator);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        return new Request([], [], [], $parameters);
    }

    protected function createAnnotation(string $name): NamedValueArgument
    {
        return new RequestCookie(array('value' => $name));
    }
}
