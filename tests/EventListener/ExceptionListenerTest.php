<?php

namespace Jungi\FrameworkExtraBundle\Tests\EventListener;

use Jungi\FrameworkExtraBundle\EventListener\ExceptionListener;
use Jungi\FrameworkExtraBundle\Http\Conversion\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Http\NotAcceptableMediaTypeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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

        $this->assertInstanceOf($expectedExceptionClass, $event->getException());
    }

    public function provideExceptions()
    {
        yield [
            BadRequestHttpException::class,
            new MalformedDataException('Data malformed'),
        ];
        yield [
            NotAcceptableHttpException::class,
            new NotAcceptableMediaTypeException(['application/xml'], ['application/json'], 'Not acceptable media type'),
        ];
    }
}
