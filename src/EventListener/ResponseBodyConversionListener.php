<?php

namespace Jungi\FrameworkExtraBundle\EventListener;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
final class ResponseBodyConversionListener implements EventSubscriberInterface
{
    private $attributeClass;
    private $responseFactory;
    private $attributeLocator;

    public static function onAttribute(ResponseFactory $responseFactory, ContainerInterface $attributeLocator): self
    {
        return new self(Attribute\ResponseBody::class, $responseFactory, $attributeLocator);
    }

    public static function onAnnotation(ResponseFactory $responseFactory, ContainerInterface $attributeLocator): self
    {
        return new self(Annotation\ResponseBody::class, $responseFactory, $attributeLocator);
    }

    public function __construct(string $attributeClass, ResponseFactory $responseFactory, ContainerInterface $attributeLocator)
    {
        $this->attributeClass = $attributeClass;
        $this->responseFactory = $responseFactory;
        $this->attributeLocator = $attributeLocator;
    }

    public function onKernelView(ViewEvent $event)
    {
        $id = RequestUtils::getControllerAsCallableString($event->getRequest());
        if (null === $id || !$this->attributeLocator->has($id) || !$this->attributeLocator->get($id)->has($this->attributeClass)) {
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
