<?php

namespace Jungi\FrameworkExtraBundle\EventListener;

use Jungi\FrameworkExtraBundle\Http\Conversion\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Http\NotAcceptableMediaTypeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ExceptionListener implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event)
    {
        $e = $event->getException();

        switch (true) {
            case $e instanceof MalformedDataException:
                $event->setException(new BadRequestHttpException('The request body is malformed.', $e));
                break;
            case $e instanceof NotAcceptableMediaTypeException:
                $event->setException(new NotAcceptableHttpException(sprintf(
                    'Could not response with any acceptable content types. Only following are supported: %s.',
                    implode(', ', $e->getSupportedMediaTypes())
                ), $e));
                break;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
