<?php

namespace Jungi\FrameworkExtraBundle\Tests\Attribute;

use Jungi\FrameworkExtraBundle\Attribute\RequestHeader;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestHeaderTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestHeader('foo');
        $this->assertEquals('foo', $annotation->name());

        $annotation = RequestHeader::__set_state(['name' => 'foo']);
        $this->assertEquals('foo', $annotation->name());
    }
}
