<?php

namespace Jungi\FrameworkExtraBundle\EventListener;

use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class ResponseBodyConversionListener implements EventSubscriberInterface
{
    private $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function onKernelView(ViewEvent $event)
    {
        $annotationRegistry = RequestUtils::getControllerAnnotationRegistry($event->getRequest());
        if (!$annotationRegistry) {
            return;
        }

        if (!$annotationRegistry->hasMethodAnnotation(ResponseBody::class)
            && !$annotationRegistry->hasClassAnnotation(ResponseBody::class)
        ) {
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
            KernelEvents::VIEW => 'onKernelView',
        ];
    }
}
