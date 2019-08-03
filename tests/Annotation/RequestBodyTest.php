<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use PHPUnit\Framework\TestCase;

class RequestBodyTest extends TestCase
{
    /** @test */
    public function create()
    {
        $this->assertNull((new RequestBody([]))->getName());
        $this->assertEquals('foo', (new RequestBody(['value' => 'foo']))->getName());
    }
}
