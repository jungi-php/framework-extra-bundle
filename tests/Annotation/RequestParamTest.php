<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestParamTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestParam(['value' => 'foo']);
        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getName());

        $annotation = new RequestParam(['value' => 'foo', 'name' => 'bar']);
        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('bar', $annotation->getName());

        $annotation = new RequestParam(['value' => 'foo', 'argument' => 'bar']);
        $this->assertEquals('bar', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getName());

        $annotation = new RequestParam(['value' => 'foo', 'argumentName' => 'bar']);
        $this->assertEquals('bar', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getName());
    }
}
