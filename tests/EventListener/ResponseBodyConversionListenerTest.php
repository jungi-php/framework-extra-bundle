<?php

namespace Jungi\FrameworkExtraBundle\Tests\EventListener;

use Jungi\FrameworkExtraBundle\Attribute\ResponseBody;
use Jungi\FrameworkExtraBundle\EventListener\ResponseBodyConversionListener;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Jungi\FrameworkExtraBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ResponseBodyConversionListenerTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideCallables
     */
    public function responseBodyMapped(callable $callable)
    {
        $request = new Request([], [], ['_controller' => $callable]);

        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $response = new Response();

        $responseFactory = $this->createMock(ResponseFactory::class);
        $responseFactory
            ->expects($this->once())
            ->method('createEntityResponse')
            ->with($request, 'foo')
            ->willReturn($response);

        $listener = new ResponseBodyConversionListener($responseFactory);

        $event = new ControllerArgumentsEvent($httpKernel, $callable, [], $request, HttpKernelInterface::MAIN_REQUEST);
        $listener->onControllerArguments($event);
        $this->assertTrue($request->attributes->get(ResponseBody::class, false));

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MAIN_REQUEST, 'foo');
        $listener->onKernelView($event);

        $this->assertTrue($event->hasResponse());
        $this->assertSame($response, $event->getResponse());
    }

    /** @test */
    public function unavailableResponseBody()
    {
        $callable = [$this, 'controller1'];
        $request = new Request([], [], ['_controller' => $callable]);

        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $listener = new ResponseBodyConversionListener($this->createMock(ResponseFactory::class));

        $event = new ControllerArgumentsEvent($httpKernel, $callable, [], $request, HttpKernelInterface::MAIN_REQUEST);
        $listener->onControllerArguments($event);
        $this->assertFalse($request->attributes->get(ResponseBody::class));

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MAIN_REQUEST, null);
        $listener->onKernelView($event);

        $this->assertFalse($event->hasResponse());
    }

    public function provideCallables(): iterable
    {
        yield [[$this, 'controller2']];
        yield [$this];
        yield [[__CLASS__, 'controller3']];
        yield [__NAMESPACE__ . '\controller4'];
    }

    public function provideCallablesWithoutAttribute(): iterable
    {
        yield [[$this, 'controller1']];
    }

    public function controller1()
    {
        return 'foo';
    }

    #[ResponseBody]
    public function controller2()
    {
        return 'foo';
    }

    #[ResponseBody]
    public static function controller3()
    {
        return 'foo';
    }

    #[ResponseBody]
    public function __invoke()
    {
        return 'foo';
    }
}

#[ResponseBody]
function controller4() {
    return 'foo';
}
