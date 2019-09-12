<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestQuery;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestQueryTest extends TestCase
{
    /** @test */
    public function create()
    {
        $this->assertEquals('foo', (new RequestQuery(['value' => 'foo']))->getName());
    }
}
