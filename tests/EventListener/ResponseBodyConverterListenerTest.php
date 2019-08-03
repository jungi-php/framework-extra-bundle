<?php

namespace Jungi\FrameworkExtraBundle\Tests\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\EventListener\ResponseBodyConverterListener;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Jungi\FrameworkExtraBundle\RequestAttributes;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\FooController;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\UserController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseBodyConverterListenerTest extends TestCase
{
    protected function setUp(): void
    {
        AnnotationRegistry::registerLoader('class_exists');
    }

    /** @test */
    public function responseBodyAnnotationOnClass()
    {
        $controller = new UserController();

        $this->assertResponseBodyConversionIsApplied([$controller, 'getResidentialAddress']);
        $this->assertResponseBodyConversionIsApplied([$controller, 'changeResidentialAddress']);
    }

    /** @test */
    public function responseBodyAnnotationOnMethod()
    {
        $this->assertResponseBodyConversionIsApplied([new FooController(), 'withResponseBody']);
    }

    /** @test */
    public function responseBodyAnnotationOnInvokeMethod()
    {
        $controller = new class() {
            /** @ResponseBody */
            public function __invoke()
            {
            }
        };

        $this->assertResponseBodyConversionIsApplied($controller);
    }

    /** @test */
    public function noResponseBodyAnnotation()
    {
        $request = new Request();
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            [new FooController(), 'plain'],
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $listener = new ResponseBodyConverterListener(new AnnotationReader(), $this->createMock(ResponseFactory::class));
        $listener->onKernelController($event);

        $this->assertFalse($request->attributes->has(RequestAttributes::REQUEST_BODY_CONVERSION));
    }

    /** @test */
    public function entityResponse()
    {
        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $response = new Response();
        $request = new Request();

        $request->attributes->set(RequestAttributes::RESPONSE_BODY_CONVERSION, true);

        $controller = [new UserController(), 'getResidentialAddress'];
        $controllerResult = $controller();

        $responseFactory = $this->createMock(ResponseFactory::class);
        $responseFactory
            ->expects($this->once())
            ->method('createEntityResponse')
            ->with($request, $controllerResult)
            ->willReturn($response);

        $listener = new ResponseBodyConverterListener(new AnnotationReader(), $responseFactory);

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MASTER_REQUEST, $controllerResult);
        $listener->onKernelView($event);

        $this->assertSame($response, $event->getResponse());
    }

    private function assertResponseBodyConversionIsApplied(callable $controller)
    {
        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $responseFactory = $this->createMock(ResponseFactory::class);
        $request = new Request();

        $listener = new ResponseBodyConverterListener(new AnnotationReader(), $responseFactory);

        $event = new ControllerEvent(
            $httpKernel,
            $controller,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $listener->onKernelController($event);

        $this->assertTrue($request->attributes->get(RequestAttributes::RESPONSE_BODY_CONVERSION));
    }
}
