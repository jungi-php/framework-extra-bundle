<?php

namespace Jungi\FrameworkExtraBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\RequestAttributes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestBodyConverterListener implements EventSubscriberInterface
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
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

        $refl = new \ReflectionMethod($controller[0], $controller[1]);
        $annotation = $this->reader->getMethodAnnotation($refl, RequestBody::class);

        if (!$annotation) {
            return;
        }

        $request = $event->getRequest();
        $request->attributes->set(RequestAttributes::REQUEST_BODY_CONVERSION, $annotation);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
