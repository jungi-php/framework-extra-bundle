<?php

namespace Jungi\FrameworkExtraBundle\Tests\EventListener;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\EventListener\ResponseBodyConversionListener;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Jungi\FrameworkExtraBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ResponseBodyConversionListenerTest extends TestCase
{
    /** @test */
    public function responseBodyMappedOnAttribute()
    {
        $request = new Request([], [], ['_controller' => 'FooController::bar']);

        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $response = new Response();

        $responseFactory = $this->createMock(ResponseFactory::class);
        $responseFactory
            ->expects($this->once())
            ->method('createEntityResponse')
            ->with($request, 'foo')
            ->willReturn($response);

        $attributeLocator = new ServiceLocator(array(
            'FooController::bar' => function() {
                return $this->createAttributeContainer([new Attribute\ResponseBody()]);
            },
        ));
        $listener = ResponseBodyConversionListener::onAttribute($responseFactory, $attributeLocator);

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MASTER_REQUEST, 'foo');
        $listener->onKernelView($event);

        $this->assertSame($response, $event->getResponse());
    }

    /** @test */
    public function responseBodyMappedOnAnnotation()
    {
        $request = new Request([], [], ['_controller' => 'FooController::bar']);

        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $response = new Response();

        $responseFactory = $this->createMock(ResponseFactory::class);
        $responseFactory
            ->expects($this->once())
            ->method('createEntityResponse')
            ->with($request, 'foo')
            ->willReturn($response);

        $attributeLocator = new ServiceLocator(array(
            'FooController::bar' => function() {
                return $this->createAttributeContainer([new Annotation\ResponseBody()]);
            },
        ));
        $listener = ResponseBodyConversionListener::onAnnotation($responseFactory, $attributeLocator);

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MASTER_REQUEST, 'foo');
        $listener->onKernelView($event);

        $this->assertSame($response, $event->getResponse());
    }

    /** @test */
    public function unavailableResponseBody()
    {
        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $listener = ResponseBodyConversionListener::onAttribute($this->createMock(ResponseFactory::class), new ServiceLocator([]));
        $request = new Request([], [], ['_controller' => 'FooController::bar']);

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MASTER_REQUEST, null);
        $listener->onKernelView($event);

        $this->assertFalse($event->hasResponse());
    }
}
