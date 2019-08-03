<?php

namespace Jungi\FrameworkExtraBundle\Tests\Http;

use Jungi\FrameworkExtraBundle\Http\UnsupportedMediaTypeException;
use PHPUnit\Framework\TestCase;

class UnsupportedMediaTypeExceptionTest extends TestCase
{
    /** @test */
    public function create()
    {
        $e = new UnsupportedMediaTypeException('application/xml', 'unsupported');
        $this->assertEquals('application/xml', $e->getMediaType());
        $this->assertEquals('unsupported', $e->getMessage());

        $e = UnsupportedMediaTypeException::mapperNotRegistered('application/json');
        $this->assertEquals('application/json', $e->getMediaType());
    }
}
