<?php

namespace Jungi\FrameworkExtraBundle\Tests\Attribute;

use Jungi\FrameworkExtraBundle\Attribute\QueryParam;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class QueryParamTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new QueryParam();
        $this->assertNull($annotation->name());

        $annotation = new QueryParam('foo');
        $this->assertEquals('foo', $annotation->name());
    }
}
