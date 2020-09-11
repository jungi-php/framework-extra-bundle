<?php

namespace Jungi\FrameworkExtraBundle\Tests\Attribute;

use Jungi\FrameworkExtraBundle\Attribute\RequestCookie;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestCookieTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestCookie();
        $this->assertNull($annotation->name());

        $annotation = new RequestCookie('foo');
        $this->assertEquals('foo', $annotation->name());

        $annotation = RequestCookie::__set_state([]);
        $this->assertNull($annotation->name());

        $annotation = RequestCookie::__set_state(['name' => 'foo']);
        $this->assertEquals('foo', $annotation->name());
    }
}
