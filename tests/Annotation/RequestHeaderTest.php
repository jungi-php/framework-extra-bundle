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
        $this->assertEquals('foo', $annotation->argument());
        $this->assertEquals('bar', $annotation->name());

        $annotation = new RequestHeader(['value' => 'foo', 'argument' => 'zoo', 'name' => 'bar']);
        $this->assertEquals('zoo', $annotation->argument());
        $this->assertEquals('bar', $annotation->name());
    }
}
