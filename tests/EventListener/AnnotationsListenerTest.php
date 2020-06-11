<?php

namespace Jungi\FrameworkExtraBundle\Tests\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Annotation\RequestQuery;
use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\EventListener\AnnotationsListener;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\FooController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class AnnotationsListenerTest extends TestCase
{
    private $listener;

    protected function setUp(): void
    {
        AnnotationRegistry::registerLoader('class_exists');

        $this->listener = new AnnotationsListener(new AnnotationReader());
    }

    /** @test */
    public function annotationsOnController()
    {
        $controller = new FooController();
        $request = new Request();

        $this->listener->onKernelController($this->createControllerEvent($request, [$controller, 'withRequestBodyParam']));
        $annotationRegistry = RequestUtils::getControllerAnnotationRegistry($request);

        $this->assertTrue($annotationRegistry->hasClassAnnotation(ResponseBody::class));
        $this->assertTrue($annotationRegistry->hasArgumentAnnotation('foo', RequestParam::class));

        $this->listener->onKernelController($this->createControllerEvent($request, [$controller, 'withRequestQueryParam']));
        $this->assertTrue(RequestUtils::getControllerAnnotationRegistry($request)->hasArgumentAnnotation('foo', QueryParam::class));

        $this->listener->onKernelController($this->createControllerEvent($request, [$controller, 'withRequestBody']));
        $this->assertTrue(RequestUtils::getControllerAnnotationRegistry($request)->hasArgumentAnnotation('foo', RequestBody::class));

        $this->listener->onKernelController($this->createControllerEvent($request, [$controller, 'withRequestQuery']));
        $this->assertTrue(RequestUtils::getControllerAnnotationRegistry($request)->hasArgumentAnnotation('foo', RequestQuery::class));
    }

    /** @test */
    public function annotationsOnInvokeMethod()
    {
        $controller = new class() {
            /** @RequestBody("data") */
            public function __invoke(\stdClass $data)
            {
            }
        };
        $request = new Request();

        $this->listener->onKernelController($this->createControllerEvent($request, $controller));
        $annotationRegistry = RequestUtils::getControllerAnnotationRegistry($request);

        $this->assertTrue($annotationRegistry->hasArgumentAnnotation('data', RequestBody::class));
    }

    private function createControllerEvent(Request $request, callable $controller): ControllerEvent
    {
        return new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            $controller,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }
}
