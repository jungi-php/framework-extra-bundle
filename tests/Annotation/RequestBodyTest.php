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
        $this->assertEquals('foo', (new RequestBody(['value' => 'foo']))->getName());
    }
}
