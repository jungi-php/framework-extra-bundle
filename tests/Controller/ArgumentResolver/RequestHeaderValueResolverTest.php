<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\RequestHeader;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestHeaderValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestHeaderValueResolverTest extends AbstractNamedValueArgumentValueResolverTest
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
        $request = $this->createRequestWithParameters(['foo' => ['one', 'second']]);
        $request->attributes->set('_controller', 'FooController');

        $argumentMetadata =  new ArgumentMetadata('foo', 'array', false, false, null, false, [
            $this->createAttribute('foo')
        ]);
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

    protected function createAttribute(string $name): NamedValue
    {
        return new RequestHeader($name);
    }
}
