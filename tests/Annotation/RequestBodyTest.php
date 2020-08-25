<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestBodyTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestBody(['value' => 'foo']);
        $this->assertEquals('foo', $annotation->argument());
        $this->assertNull($annotation->type());

        $annotation = new RequestBody(['value' => 'zoo', 'argument' => 'foo', 'type' => 'int[]']);
        $this->assertEquals('foo', $annotation->argument());
        $this->assertEquals('int[]', $annotation->type());
    }

    /** @test */
    public function invalid()
    {
        $this->expectException(\InvalidArgumentException::class);

        new RequestBody(['value' => 'foo', 'type' => 'array']);
    }
}
