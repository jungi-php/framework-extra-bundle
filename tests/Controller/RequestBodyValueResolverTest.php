<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Controller\RequestBodyValueResolver;
use Jungi\FrameworkExtraBundle\Http\Conversion\MessageBodyConversionManager;
use Jungi\FrameworkExtraBundle\Http\UnsupportedMediaTypeException;
use Jungi\FrameworkExtraBundle\RequestAttributes;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class RequestBodyValueResolverTest extends TestCase
{
    /** @test */
    public function supports()
    {
        $valueResolver = new RequestBodyValueResolver($this->createMock(MessageBodyConversionManager::class));

        $request = new Request();
        $request->attributes->set(RequestAttributes::REQUEST_BODY_CONVERSION, new RequestBody(['value' => 'foo']));

        $this->assertTrue($valueResolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null)));
        $this->assertFalse($valueResolver->supports($request, new ArgumentMetadata('bar', 'stdClass', false, false, null)));

        $request = new Request();
        $request->attributes->set('_other', '');

        $this->assertFalse($valueResolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null)));
    }

    /** @test */
    public function resolve()
    {
        $expected = new \stdClass();
        $expected->hello = 'world';

        $request = new Request([], [], [], [], [], [], '{"hello": "world"}');
        $request->headers->set('Content-Type', 'application/json');

        $argument = new ArgumentMetadata('foo', 'stdClass', false, false, null);

        $conversionManagerMock = $this->createMock(MessageBodyConversionManager::class);
        $conversionManagerMock
            ->expects($this->once())
            ->method('convertFromInputMessage')
            ->with(
                $request->getContent(),
                $argument->getType(),
                $request->headers->get('CONTENT_TYPE')
            )
            ->willReturn($expected);

        $valueResolver = new RequestBodyValueResolver($conversionManagerMock);

        $this->assertEquals($expected, $valueResolver->resolve($request, $argument)->current());
    }

    /** @test */
    public function resolveOnInvalidArgumentType()
    {
        $this->expectException(\InvalidArgumentException::class);

        $valueResolver = new RequestBodyValueResolver($this->createMock(MessageBodyConversionManager::class));
        $valueResolver->resolve(new Request(), new ArgumentMetadata('foo', 'string', false, false, null))->current();
    }

    /** @test */
    public function resolveOnUnsupportedMediaType()
    {
        $this->expectException(UnsupportedMediaTypeHttpException::class);

        $conversionManager = $this->createMock(MessageBodyConversionManager::class);
        $conversionManager
            ->expects($this->once())
            ->method('convertFromInputMessage')
            ->willThrowException(UnsupportedMediaTypeException::mapperNotRegistered('application/json'));

        $request = new Request();
        $request->headers->set('Content-Type', 'application/json');

        $valueResolver = new RequestBodyValueResolver($conversionManager);
        $valueResolver->resolve($request, new ArgumentMetadata('foo', 'stdClass', false, false, null))->current();
    }

    /** @test */
    public function resolveOnMissingRequestContentType()
    {
        $this->expectException(BadRequestHttpException::class);

        $valueResolver = new RequestBodyValueResolver($this->createMock(MessageBodyConversionManager::class));
        $valueResolver->resolve(new Request(), new ArgumentMetadata('foo', 'stdClass', false, false, null))->current();
    }
}
