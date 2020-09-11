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
        $this->assertEquals('foo', $annotation->argument());
        $this->assertEquals('foo', $annotation->name());

        $annotation = new QueryParam(['value' => 'foo', 'name' => 'bar']);
        $this->assertEquals('foo', $annotation->argument());
        $this->assertEquals('bar', $annotation->name());

        $annotation = new QueryParam(['value' => 'foo', 'argument' => 'bar']);
        $this->assertEquals('bar', $annotation->argument());
        $this->assertEquals('foo', $annotation->name());

        $annotation = QueryParam::__set_state(['name' => 'foo', 'argument' => 'bar']);
        $this->assertEquals('bar', $annotation->argument());
        $this->assertEquals('foo', $annotation->name());
    }
}
