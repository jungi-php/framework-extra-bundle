<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\QueryParams;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class QueryParamsTest extends TestCase
{
    /** @test */
    public function create()
    {
        $this->assertEquals('foo', (new QueryParams(['value' => 'foo']))->argument());
        $this->assertEquals('bar', (new QueryParams(['value' => 'foo', 'argument' => 'bar']))->argument());
        $this->assertEquals('bar', (QueryParams::__set_state(['argument' => 'bar']))->argument());
    }
}
