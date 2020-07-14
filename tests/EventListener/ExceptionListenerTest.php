<?php

namespace Jungi\FrameworkExtraBundle\Tests\EventListener;

use Jungi\FrameworkExtraBundle\EventListener\ExceptionListener;
use Jungi\FrameworkExtraBundle\Http\UnsupportedMediaTypeException;
use Jungi\FrameworkExtraBundle\Http\NotAcceptableMediaTypeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ExceptionListenerTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideExceptions
     */
    public function exceptionIsReplaced(string $expectedExceptionClass, \Exception $thrown)
    {
        $listener = new ExceptionListener();

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST,
            $thrown
        );
        $listener->onKernelException($event);

        $this->assertInstanceOf($expectedExceptionClass, $event->getThrowable());
    }

    public function provideExceptions()
    {
        yield [
            UnsupportedMediaTypeHttpException::class,
            new UnsupportedMediaTypeException('application/xml', 'Unsupported media type.'),
        ];
        yield [
            NotAcceptableHttpException::class,
            new NotAcceptableMediaTypeException(['application/xml'], ['application/json'], 'Not acceptable media type'),
        ];
    }
}
