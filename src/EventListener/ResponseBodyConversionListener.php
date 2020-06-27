<?php

namespace Jungi\FrameworkExtraBundle\EventListener;

use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class ResponseBodyConversionListener implements EventSubscriberInterface
{
    private $responseFactory;
    private $annotationLocator;

    public function __construct(ResponseFactory $responseFactory, ContainerInterface $annotationLocator)
    {
        $this->responseFactory = $responseFactory;
        $this->annotationLocator = $annotationLocator;
    }

    public function onKernelView(ViewEvent $event)
    {
        $id = RequestUtils::getControllerAsCallableSyntax($event->getRequest());

        if (!$this->annotationLocator->has($id) || !$this->annotationLocator->get($id)->has(ResponseBody::class)) {
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
