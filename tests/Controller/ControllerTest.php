<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller;

use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\FooController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ControllerTest extends TestCase
{
    /** @test */
    public function entity()
    {
        $request = new Request();
        $entity = array('hello' => 'world');
        $status = 201;
        $headers = ['Custom' => 'foo'];

        $responseFactory = $this->createMock(ResponseFactory::class);
        $responseFactory
            ->expects($this->once())
            ->method('createEntityResponse')
            ->with($request, $entity, $status, $headers);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $container = new Container();
        $container->set('request_stack', $requestStack);
        $container->set('jungi.response_factory', $responseFactory);

        $controller = new FooController();
        $controller->setContainer($container);

        $controller->withEntity($entity, $status, $headers);
    }
}
