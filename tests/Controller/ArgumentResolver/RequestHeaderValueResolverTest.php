<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\ClassMethodAnnotationRegistry;
use Jungi\FrameworkExtraBundle\Annotation\RequestFieldAnnotationInterface;
use Jungi\FrameworkExtraBundle\Annotation\RequestHeader;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestHeaderValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestHeaderValueResolverTest extends AbstractRequestFieldValueResolverTest
{
    public function argumentTypeSameAsParameterType()
    {
        $this->markTestSkipped('always as string value');
    }

    /** @test */
    public function argumentOfArrayType()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createArgumentValueResolver($converter);
        $request = new Request();
        $request->headers->set('foo', ['one', 'second']);

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            new RequestHeader(array('argument' => 'foo', 'field' => 'foo')),
        ]));

        $argumentMetadata =  new ArgumentMetadata('foo', 'array', false, false, null);

        $this->assertEquals(['one', 'second'], $resolver->resolve($request, $argumentMetadata)->current());
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
