<?php

namespace Jungi\FrameworkExtraBundle\Tests\Http;

use Jungi\FrameworkExtraBundle\Http\NotAcceptableMediaTypeException;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class NotAcceptableMediaTypeExceptionTest extends TestCase
{
    /** @test */
    public function create()
    {
        $expectedNotAcceptable = ['text/xml', 'text/csv'];
        $expectedSupported = ['application/xml', 'application/json'];
        $expectedMessage = 'not acceptable';

        $e = new NotAcceptableMediaTypeException($expectedNotAcceptable, $expectedSupported, $expectedMessage);

        $this->assertEquals($expectedNotAcceptable, $e->getNotAcceptableMediaTypes());
        $this->assertEquals($expectedSupported, $e->getSupportedMediaTypes());
        $this->assertEquals($expectedMessage, $e->getMessage());
    }
}
