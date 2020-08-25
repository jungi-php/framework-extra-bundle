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
        $this->assertEquals('foo', $annotation->argument());
        $this->assertEquals('foo', $annotation->name());

        $annotation = new RequestCookie(['value' => 'foo', 'name' => 'bar']);
        $this->assertEquals('foo', $annotation->argument());
        $this->assertEquals('bar', $annotation->name());

        $annotation = new RequestCookie(['value' => 'foo', 'argument' => 'bar']);
        $this->assertEquals('bar', $annotation->argument());
        $this->assertEquals('foo', $annotation->name());
    }
}
