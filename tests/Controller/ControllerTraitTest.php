<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller;

use Jungi\FrameworkExtraBundle\Controller\ControllerTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ControllerTraitTest extends TestCase
{
    /** @test */
    public function entity()
    {
        $controller = new TestController();
        $response = $controller->entity('foo', 201, ['Foo' => 'bar']);

        $this->assertEquals('foo', $response->getEntity());
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertSame('bar', $response->headers->get('Foo'));
    }
}

class TestController
{
    use ControllerTrait;

    public function __call(string $method, array $args): mixed
    {
        return $this->{$method}(...$args);
    }
}