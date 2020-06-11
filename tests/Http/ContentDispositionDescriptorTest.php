<?php

namespace Jungi\FrameworkExtraBundle\Tests\Http;

use Jungi\FrameworkExtraBundle\Http\ContentDispositionDescriptor;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ContentDispositionDescriptorTest extends TestCase
{
    /** @test */
    public function parse()
    {
        $contentDisposition = ContentDispositionDescriptor::parse('inline; filename = foo123.csv; foo = "long value"');

        $this->assertTrue($contentDisposition->isInline());
        $this->assertEquals('foo123.csv', $contentDisposition->getFilename());
        $this->assertEquals('long value', $contentDisposition->getParam('foo'));
    }

    /** @test */
    public function parseMinimal()
    {
        $contentDisposition = ContentDispositionDescriptor::parse('custom');

        $this->assertEquals('custom', $contentDisposition->getType());
    }

    /** @test */
    public function parseWithBlankParams()
    {
        $contentDisposition = ContentDispositionDescriptor::parse('inline;;');

        $this->assertEquals('inline', $contentDisposition->getType());
        $this->assertEmpty($contentDisposition->getParams());
    }

    /**
     * @test
     * @dataProvider provideInvalidContentDispositions
     */
    public function parseInvalid($contentDisposition)
    {
        $this->expectException(\InvalidArgumentException::class);

        ContentDispositionDescriptor::parse($contentDisposition);
    }

    /** @test */
    public function create()
    {
        $params = array(
            'filename' => 'foo123.csv',
            'foo' => 'bar',
        );
        $contentDisposition = new ContentDispositionDescriptor('inline', $params);

        $this->assertTrue($contentDisposition->isInline());
        $this->assertEquals('inline', $contentDisposition->getType());
        $this->assertEquals($params, $contentDisposition->getParams());
        $this->assertTrue($contentDisposition->hasParam('foo'));
        $this->assertEquals('bar', $contentDisposition->getParam('foo'));
        $this->assertEquals('foo123.csv', $contentDisposition->getFilename());
        $this->assertFalse($contentDisposition->hasParam('invalid'));
        $this->assertNull($contentDisposition->getParam('invalid'));

        $params = array(
            'foo' => 'bar',
            'hello' => 'world'
        );
        $contentDisposition = new ContentDispositionDescriptor('attachment', $params);

        $this->assertFalse($contentDisposition->isInline());
        $this->assertNull($contentDisposition->getFilename());
        $this->assertEquals('attachment', $contentDisposition->getType());
    }

    public function provideInvalidContentDispositions()
    {
        yield [''];
        yield ['inline; filename = foo123.csv; foo'];
        yield ['inline; filename = foo123.csv; ='];
    }
}
