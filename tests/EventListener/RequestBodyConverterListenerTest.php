<?php

namespace Jungi\FrameworkExtraBundle\Tests\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\EventListener\RequestBodyConverterListener;
use Jungi\FrameworkExtraBundle\RequestAttributes;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\UserController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestBodyConverterListenerTest extends TestCase
{
    private $listener;

    protected function setUp(): void
    {
        AnnotationRegistry::registerLoader('class_exists');

        $this->listener = new RequestBodyConverterListener(new AnnotationReader());
    }

    /** @test */
    public function requestBodyAnnotationOnMethod()
    {
        $this->assertRequestBodyAnnotationIsApplied([new UserController(), 'changeResidentialAddress']);
    }

    /** @test */
    public function requestBodyAnnotationOnInvokeMethod()
    {
        $controller = new class() {
            /** @RequestBody */
            public function __invoke()
            {
            }
        };

        $this->assertRequestBodyAnnotationIsApplied($controller);
    }

    /** @test */
    public function noRequestBodyAnnotation()
    {
        $request = new Request();
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            [new UserController(), 'getResidentialAddress'],
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->listener->onKernelController($event);

        $this->assertFalse($request->attributes->has(RequestAttributes::REQUEST_BODY_CONVERSION));
    }

    private function assertRequestBodyAnnotationIsApplied(callable $controller)
    {
        $request = new Request();
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            $controller,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->listener->onKernelController($event);

        $this->assertTrue($request->attributes->has(RequestAttributes::REQUEST_BODY_CONVERSION));
        $this->assertInstanceOf(RequestBody::class, $request->attributes->get(RequestAttributes::REQUEST_BODY_CONVERSION));
    }
}
