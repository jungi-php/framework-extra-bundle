<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestFieldAnnotationInterface;
use Jungi\FrameworkExtraBundle\Annotation\RequestHeader;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestHeaderValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestHeaderValueResolverTest extends AbstractRequestFieldValueResolverTest
{
    public function argumentTypeSameAsParameterType()
    {
        $this->markTestSkipped('always as string value');
    }

    protected function createArgumentValueResolver(ConverterInterface $converter): ArgumentValueResolverInterface
    {
        return new RequestHeaderValueResolver($converter);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        $request = new Request();
        $request->headers->replace($parameters);

        return $request;
    }

    protected function createAnnotation(string $name): RequestFieldAnnotationInterface
    {
        return new RequestHeader(array('argument' => $name, 'field' => $name));
    }
}
