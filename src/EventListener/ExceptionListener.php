<?php

namespace Jungi\FrameworkExtraBundle\EventListener;

use Jungi\FrameworkExtraBundle\Http\NotAcceptableMediaTypeException;
use Jungi\FrameworkExtraBundle\Http\UnsupportedMediaTypeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class ExceptionListener implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $this->translateToHttpException($event->getThrowable());
        if ($exception) {
            $event->setThrowable($exception);
        }
    }

    public function onKernelExceptionBC(ExceptionEvent $event)
    {
        $exception = $this->translateToHttpException($event->getException());
        if ($exception) {
            $event->setException($exception);
        }
    }

    private function translateToHttpException(\Throwable $e): ?HttpException
    {
        switch (true) {
            case $e instanceof NotAcceptableMediaTypeException:
                return new NotAcceptableHttpException(sprintf(
                    'Could not response with any acceptable content types. Only following are supported: %s.',
                    implode(', ', $e->getSupportedMediaTypes())
                ), $e);
                break;
            case $e instanceof UnsupportedMediaTypeException:
                return new UnsupportedMediaTypeHttpException(sprintf(
                    'Content type "%s" is not supported.',
                    $e->getMediaType()
                ), $e);
                break;
            default:
                return null;
        }
    }

    public static function getSubscribedEvents()
    {
        if (method_exists(ExceptionEvent::class, 'getThrowable')) {
            return [
                KernelEvents::EXCEPTION => 'onKernelException',
            ];
        }

        return [
            KernelEvents::EXCEPTION => 'onKernelExceptionBC',
        ];
    }
}
