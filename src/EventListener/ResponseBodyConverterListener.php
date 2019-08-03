<?php

namespace Jungi\FrameworkExtraBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Jungi\FrameworkExtraBundle\RequestAttributes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ResponseBodyConverterListener implements EventSubscriberInterface
{
    private $reader;
    private $responseFactory;

    public function __construct(Reader $reader, ResponseFactory $responseFactory)
    {
        $this->reader = $reader;
        $this->responseFactory = $responseFactory;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
        }

        if (!is_array($controller)) {
            return;
        }

        $refl = new \ReflectionClass($controller[0]);
        $annotation = $this->reader->getClassAnnotation($refl, ResponseBody::class);

        if (!$annotation) {
            $refl = new \ReflectionMethod($controller[0], $controller[1]);
            $annotation = $this->reader->getMethodAnnotation($refl, ResponseBody::class);
        }

        if (!$annotation) {
            return;
        }

        $request = $event->getRequest();
        $request->attributes->set(RequestAttributes::RESPONSE_BODY_CONVERSION, true);
    }

    public function onKernelView(ViewEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(RequestAttributes::RESPONSE_BODY_CONVERSION)) {
            return;
        }

        $event->setResponse($this->responseFactory->createEntityResponse(
            $event->getRequest(),
            $event->getControllerResult()
        ));
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::VIEW => 'onKernelView',
        ];
    }
}
