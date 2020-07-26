<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestHeader;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestHeaderTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestHeader(['value' => 'foo', 'name' => 'bar']);
        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('bar', $annotation->getName());

        $annotation = new RequestHeader(['value' => 'foo', 'argument' => 'zoo', 'name' => 'bar']);
        $this->assertEquals('zoo', $annotation->getArgumentName());
        $this->assertEquals('bar', $annotation->getName());

        $annotation = new RequestHeader(['argumentName' => 'foo', 'name' => 'bar']);
        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('bar', $annotation->getName());
    }
}
