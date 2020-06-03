<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\RequestCookie;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestCookieTest extends TestCase
{
    /** @test */
    public function create()
    {
        $annotation = new RequestCookie(array('value' => 'foo'));

        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getFieldName());

        $annotation = new RequestCookie(array(
            'value' => 'foo',
            'field' => 'bar',
        ));

        $this->assertEquals('foo', $annotation->getArgumentName());
        $this->assertEquals('bar', $annotation->getFieldName());

        $annotation = new RequestCookie(array(
            'value' => 'foo',
            'argument' => 'bar',
        ));

        $this->assertEquals('bar', $annotation->getArgumentName());
        $this->assertEquals('foo', $annotation->getFieldName());
    }
}
