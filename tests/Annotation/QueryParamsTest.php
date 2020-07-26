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
        $this->assertEquals('foo', (new QueryParams(['value' => 'foo']))->getArgumentName());
        $this->assertEquals('foo', (new QueryParams(['argumentName' => 'foo']))->getArgumentName());
        $this->assertEquals('bar', (new QueryParams(['value' => 'foo', 'argument' => 'bar']))->getArgumentName());
    }
}
