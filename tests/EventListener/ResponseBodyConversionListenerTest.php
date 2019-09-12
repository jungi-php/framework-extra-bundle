<?php

namespace Jungi\FrameworkExtraBundle\Tests\EventListener;

use Jungi\FrameworkExtraBundle\Annotation\ClassMethodAnnotationRegistry;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\EventListener\ResponseBodyConversionListener;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use PHPUnit\Framework\TestCase;
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
    public function responseBodyOnClass()
    {
        $this->assertEntityResponse(new ClassMethodAnnotationRegistry([new ResponseBody()], [], []));
    }

    /** @test */
    public function responseBodyOnMethod()
    {
        $this->assertEntityResponse(new ClassMethodAnnotationRegistry([], [new ResponseBody()], []));
    }

    /** @test */
    public function unavailableResponseBody()
    {
        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], []));

        $listener = new ResponseBodyConversionListener($this->createMock(ResponseFactory::class));

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MASTER_REQUEST, null);
        $listener->onKernelView($event);

        $this->assertFalse($event->hasResponse());
    }

    /** @test */
    public function unavailableControllerAnnotationRegistry()
    {
        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        $listener = new ResponseBodyConversionListener($this->createMock(ResponseFactory::class));

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MASTER_REQUEST, null);
        $listener->onKernelView($event);

        $this->assertFalse($event->hasResponse());
    }

    private function assertEntityResponse(ClassMethodAnnotationRegistry $annotationRegistry)
    {
        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $response = new Response();
        $request = new Request();

        RequestUtils::setControllerAnnotationRegistry($request, $annotationRegistry);

        $responseFactory = $this->createMock(ResponseFactory::class);
        $responseFactory
            ->expects($this->once())
            ->method('createEntityResponse')
            ->with($request, 'foo')
            ->willReturn($response);

        $listener = new ResponseBodyConversionListener($responseFactory);

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MASTER_REQUEST, 'foo');
        $listener->onKernelView($event);

        $this->assertSame($response, $event->getResponse());
    }
}
