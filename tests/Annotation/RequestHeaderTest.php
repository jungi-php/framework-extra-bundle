<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestHeader;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestHeaderTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestHeader(array(
            'value' => 'foo',
            'field' => 'bar',
        ));

        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('bar', $annotation->getFieldName());

        $annotation = new RequestHeader(array(
            'value' => 'foo',
            'argument' => 'zoo',
            'field' => 'bar',
        ));

        $this->assertEquals('zoo', $annotation->getArgumentName());
        $this->assertEquals('bar', $annotation->getFieldName());
    }
}
