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
        $this->assertEquals('foo', $annotation->argument());
        $this->assertEquals('foo', $annotation->name());

        $annotation = new RequestParam(['value' => 'foo', 'name' => 'bar']);
        $this->assertEquals('foo', $annotation->argument());
        $this->assertEquals('bar', $annotation->name());

        $annotation = new RequestParam(['value' => 'foo', 'argument' => 'bar']);
        $this->assertEquals('bar', $annotation->argument());
        $this->assertEquals('foo', $annotation->name());
    }
}
