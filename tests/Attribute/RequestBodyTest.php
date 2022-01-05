<?php

namespace Jungi\FrameworkExtraBundle\Tests\Attribute;

use Jungi\FrameworkExtraBundle\Attribute\RequestBody;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestBodyTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestBody();
        $this->assertNull($annotation->type());

        $annotation = new RequestBody('int[]');
        $this->assertEquals('int[]', $annotation->type());
    }
}
