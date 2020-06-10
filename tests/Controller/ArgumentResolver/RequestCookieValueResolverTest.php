<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestCookie;
use Jungi\FrameworkExtraBundle\Annotation\RequestFieldAnnotationInterface;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestCookieValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestCookieValueResolverTest extends AbstractRequestFieldValueResolverTest
{
    public function argumentTypeSameAsParameterType()
    {
        $this->markTestSkipped('always as string value');
    }

    protected function createArgumentValueResolver(ConverterInterface $converter): ArgumentValueResolverInterface
    {
        return new RequestCookieValueResolver($converter);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        return new Request([], [], [], $parameters);
    }

    protected function createAnnotation(string $name): RequestFieldAnnotationInterface
    {
        return new RequestCookie(array('value' => $name));
    }
}