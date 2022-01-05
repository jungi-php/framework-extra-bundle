<?php

namespace Jungi\FrameworkExtraBundle\Tests\Attribute;

use Jungi\FrameworkExtraBundle\Attribute\RequestParam;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestParamTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestParam();
        $this->assertNull($annotation->name());

        $annotation = new RequestParam('foo');
        $this->assertEquals('foo', $annotation->name());
    }
}
