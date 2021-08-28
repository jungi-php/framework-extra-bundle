<?php

namespace Jungi\FrameworkExtraBundle\Tests\EventListener;

use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute\ResponseBody;
use Jungi\FrameworkExtraBundle\EventListener\ResponseBodyConversionListener;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Jungi\FrameworkExtraBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
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

        $listener = new ResponseBodyConversionListener($responseFactory, new ServiceLocator(array(
            self::class.'::controller1' => function() {
                return $this->createAnnotationContainer([new Annotation\ResponseBody()]);
            },
        )));

        $event = new ControllerArgumentsEvent($httpKernel, $callable, [], $request, HttpKernelInterface::MAIN_REQUEST);
        $listener->onControllerArguments($event);
        $this->assertTrue($request->attributes->get(Attribute\ResponseBody::class, false));

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MAIN_REQUEST, 'foo');
        $listener->onKernelView($event);

        $this->assertTrue($event->hasResponse());
        $this->assertSame($response, $event->getResponse());
    }

    /** @test */
    public function unavailableResponseBody()
    {
        $callable = [self::class, 'controller1'];
        $request = new Request([], [], ['_controller' => $callable]);

        $httpKernel = $this->createMock(HttpKernelInterface::class);
        $listener = new ResponseBodyConversionListener($this->createMock(ResponseFactory::class), new ServiceLocator(array(
            self::class.'::differentController' => function() {
                return $this->createAnnotationContainer([new Annotation\ResponseBody()]);
            },
        )));

        $event = new ControllerArgumentsEvent($httpKernel, $callable, [], $request, HttpKernelInterface::MAIN_REQUEST);
        $listener->onControllerArguments($event);
        $this->assertFalse($request->attributes->get(ResponseBody::class));

        $event = new ViewEvent($httpKernel, $request, HttpKernelInterface::MAIN_REQUEST, null);
        $listener->onKernelView($event);

        $this->assertFalse($event->hasResponse());
    }

    public function provideCallables(): iterable
    {
        yield [[self::class, 'controller1']];

        if (PHP_VERSION_ID >= 80000) {
            yield [[$this, 'controller2']];
            yield [$this];
            yield [__NAMESPACE__ . '\controller3'];
        }
    }

    public function provideCallablesWithoutAttribute(): iterable
    {
        yield [[self::class, 'controller1']];
    }

    public static function controller1()
    {
        return 'foo';
    }

    #[Attribute\ResponseBody]
    public function controller2()
    {
        return 'foo';
    }

    #[Attribute\ResponseBody]
    public function __invoke()
    {
        return 'foo';
    }
}

#[Attribute\ResponseBody]
function controller3() {
    return 'foo';
}
