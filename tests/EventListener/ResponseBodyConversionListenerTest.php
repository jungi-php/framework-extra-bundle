<?php

namespace Jungi\FrameworkExtraBundle\Tests\EventListener;

use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
use Jungi\FrameworkExtraBundle\EventListener\ResponseBodyConversionListener;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
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
    public function responseBodyMapped()
    {
        $annotationLocator = new ServiceLocator(array(
            'FooController' => function() {
                return new SimpleContainer(array(
                    ResponseBody::class => new ResponseBody()
                ));
            },
            'FooController::bar' => function() {
                return new SimpleContainer(array(
                    ResponseBody::class => new ResponseBody()
                ));
            },
        ));
        $this->assertEntityResponse(new Request([], [], ['_controller' => 'FooController']), $annotationLocator);
        $this->assertEntityResponse(new Request([], [], ['_controller' => 'FooController::bar']), $annotationLocator);
    }

    /** @test */
    public function unavailableResponseBody()
    {
        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $listener = new ResponseBodyConversionListener($this->createMock(ResponseFactory::class), new ServiceLocator([]));
        $request = new Request([], [], ['_controller' => 'FooController']);

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MASTER_REQUEST, null);
        $listener->onKernelView($event);

        $this->assertFalse($event->hasResponse());
    }

    private function assertEntityResponse(Request $request, ContainerInterface $annotationLocator)
    {
        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $response = new Response();

        $responseFactory = $this->createMock(ResponseFactory::class);
        $responseFactory
            ->expects($this->once())
            ->method('createEntityResponse')
            ->with($request, 'foo')
            ->willReturn($response);

        $listener = new ResponseBodyConversionListener($responseFactory, $annotationLocator);

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MASTER_REQUEST, 'foo');
        $listener->onKernelView($event);

        $this->assertSame($response, $event->getResponse());
    }
}
