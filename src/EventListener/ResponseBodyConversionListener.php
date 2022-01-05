<?php

namespace Jungi\FrameworkExtraBundle\EventListener;

use Jungi\FrameworkExtraBundle\Attribute\ResponseBody;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
final class ResponseBodyConversionListener implements EventSubscriberInterface
{
    private $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function onControllerArguments(ControllerArgumentsEvent $event)
    {
        $request = $event->getRequest();
        $controller = $event->getController();

        if (\is_array($controller)) {
            $reflection = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (\is_object($controller) && !$controller instanceof \Closure) {
            $reflection = (new \ReflectionObject($controller))->getMethod('__invoke');
        } else {
            $reflection = new \ReflectionFunction($controller);
        }

        $hasResponseBody = (bool) $reflection->getAttributes(ResponseBody::class, \ReflectionAttribute::IS_INSTANCEOF);
        $request->attributes->set(ResponseBody::class, $hasResponseBody);
    }

    public function onKernelView(ViewEvent $event)
    {
        if ($event->getRequest()->attributes->get(ResponseBody::class, false)) {
            $event->setResponse($this->responseFactory->createEntityResponse(
                $event->getRequest(),
                $event->getControllerResult()
            ));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onControllerArguments', -256],
            KernelEvents::VIEW => 'onKernelView',
        ];
    }
}
