<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class QueryParamTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new QueryParam(array('value' => 'foo'));

        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getFieldName());

        $annotation = new QueryParam(array(
            'value' => 'foo',
            'field' => 'bar',
        ));

        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('bar', $annotation->getFieldName());

        $annotation = new QueryParam(array(
            'value' => 'foo',
            'argument' => 'bar',
        ));

        $this->assertEquals('bar', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getFieldName());
    }
}
