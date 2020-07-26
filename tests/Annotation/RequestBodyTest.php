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
        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertNull($annotation->getArgumentType());

        $annotation = new RequestBody(['argument' => 'foo', 'type' => 'int[]']);
        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('int[]', $annotation->getArgumentType());

        $annotation = new RequestBody(['argumentName' => 'foo', 'argumentType' => 'int[][]']);
        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('int[][]', $annotation->getArgumentType());
    }

    /** @test */
    public function invalid()
    {
        $this->expectException(\InvalidArgumentException::class);

        new RequestBody(['value' => 'foo', 'type' => 'array']);
    }
}
