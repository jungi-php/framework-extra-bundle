<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class QueryParamTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new QueryParam(['value' => 'foo']);
        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getName());

        $annotation = new QueryParam(['value' => 'foo', 'name' => 'bar']);
        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('bar', $annotation->getName());

        $annotation = new QueryParam(['value' => 'foo', 'argument' => 'bar']);
        $this->assertEquals('bar', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getName());

        $annotation = new QueryParam(['value' => 'foo', 'argumentName' => 'bar']);
        $this->assertEquals('bar', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getName());
    }
}
