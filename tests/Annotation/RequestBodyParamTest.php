<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestBodyParam;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestBodyParamTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestBodyParam(array(
            'value' => 'foo',
            'field' => 'bar'
        ));
        $this->assertEquals('foo', $annotation->getName());
        $this->assertEquals('bar', $annotation->getField());
    }
}
