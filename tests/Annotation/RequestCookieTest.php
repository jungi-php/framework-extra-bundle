<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestCookie;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestCookieTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestCookie(['value' => 'foo']);
        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getName());

        $annotation = new RequestCookie(['value' => 'foo', 'name' => 'bar']);
        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('bar', $annotation->getName());

        $annotation = new RequestCookie(['value' => 'foo', 'argument' => 'bar']);
        $this->assertEquals('bar', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getName());

        $annotation = new RequestCookie(['value' => 'foo', 'argumentName' => 'bar']);
        $this->assertEquals('bar', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getName());
    }
}
