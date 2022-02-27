<?php

namespace Jungi\FrameworkExtraBundle\Tests\EventListener;

use Jungi\FrameworkExtraBundle\EventListener\EntityResponseListener;
use Jungi\FrameworkExtraBundle\Http\EntityResponse;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class EntityResponseListenerTest extends TestCase
{
    private HttpKernelInterface $kernel;
    private MessageBodyMapperManager $messageBodyMapperManager;

    protected function setUp(): void
    {
        $this->kernel = $this->createMock(HttpKernelInterface::class);
        $this->messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
    }

    /** @test */
    public function testContentIsNegotiated()
    {
        $response = $this->createMock(EntityResponse::class);
        $response
            ->expects($this->once())
            ->method('negotiateContent')
            ->with(
                $this->isInstanceOf(Request::class),
                $this->isInstanceOf(MessageBodyMapperManager::class),
                'application/xml'
            );

        $listener = new EntityResponseListener($this->messageBodyMapperManager, 'application/xml');

        $event = new ResponseEvent($this->kernel, new Request(), HttpKernelInterface::MAIN_REQUEST, $response);
        $listener->onKernelResponse($event);
    }

    public function testContentIsNotNegotiatedForSubRequests()
    {
        $response = $this->createMock(EntityResponse::class);
        $response
            ->expects($this->never())
            ->method('negotiateContent');

        $listener = new EntityResponseListener($this->messageBodyMapperManager);

        $event = new ResponseEvent($this->kernel, new Request(), HttpKernelInterface::SUB_REQUEST, $response);
        $listener->onKernelResponse($event);
    }
}