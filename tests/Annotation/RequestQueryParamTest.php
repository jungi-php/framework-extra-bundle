<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestQueryParam;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestQueryParamTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestQueryParam(array(
            'value' => 'foo',
            'field' => 'bar'
        ));
        $this->assertEquals('foo', $annotation->getName());
        $this->assertEquals('bar', $annotation->getField());
    }
}
