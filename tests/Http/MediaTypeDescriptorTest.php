<?php

namespace Jungi\FrameworkExtraBundle\Tests\Http;

use Jungi\FrameworkExtraBundle\Http\MediaTypeDescriptor;
use PHPUnit\Framework\TestCase;

class MediaTypeDescriptorTest extends TestCase
{
    /** @test */
    public function parse()
    {
        $descriptor = MediaTypeDescriptor::parse('application/json');

        $this->assertEquals('application', $descriptor->getType());
        $this->assertEquals('json', $descriptor->getSubType());
    }

    /** @test */
    public function parseOrNull()
    {
        $descriptor = MediaTypeDescriptor::parseOrNull('application/json');

        $this->assertEquals('application', $descriptor->getType());
        $this->assertEquals('json', $descriptor->getSubType());

        $this->assertNull(MediaTypeDescriptor::parseOrNull('invalid'));
    }

    /**
     * @test
     * @dataProvider provideInvalidInputs
     */
    public function parseInvalidInput(string $mediaType)
    {
        $this->expectException(\InvalidArgumentException::class);

        MediaTypeDescriptor::parse($mediaType);
    }

    /** @test */
    public function parseList()
    {
        list($json, $xml) = MediaTypeDescriptor::parseList(['application/json', 'text/xml']);

        $this->assertEquals('application', $json->getType());
        $this->assertEquals('json', $json->getSubType());

        $this->assertEquals('text', $xml->getType());
        $this->assertEquals('xml', $xml->getSubType());
    }

    /** @test */
    public function create()
    {
        $descriptor = new MediaTypeDescriptor('application', 'xml');

        $this->assertEquals('application', $descriptor->getType());
        $this->assertEquals('xml', $descriptor->getSubType());
        $this->assertEquals('application/xml', $descriptor->toString());
        $this->assertTrue($descriptor->isSpecific());
        $this->assertFalse($descriptor->isRange());

        $descriptor = new MediaTypeDescriptor('application', '*');

        $this->assertEquals('application', $descriptor->getType());
        $this->assertEquals('*', $descriptor->getSubType());
        $this->assertEquals('application/*', $descriptor->toString());
        $this->assertFalse($descriptor->isSpecific());
        $this->assertTrue($descriptor->isRange());

        $descriptor = new MediaTypeDescriptor('*', '*');

        $this->assertEquals('*', $descriptor->getType());
        $this->assertEquals('*', $descriptor->getSubType());
        $this->assertEquals('*/*', $descriptor->toString());
        $this->assertFalse($descriptor->isSpecific());
        $this->assertTrue($descriptor->isRange());
    }

    /** @test */
    public function createWithMisplacedWildcard()
    {
        $this->expectException(\InvalidArgumentException::class);

        new MediaTypeDescriptor('*', 'xml');
    }

    /**
     * @test
     * @dataProvider provideMediaTypesInRange
     */
    public function isMediaTypeInRangeOf($mediaType, $checkedMediaType)
    {
        $descriptor = MediaTypeDescriptor::parse($mediaType);
        $checkedDescriptor = MediaTypeDescriptor::parse($checkedMediaType);

        $this->assertTrue($descriptor->inRange($checkedDescriptor));
    }

    /**
     * @test
     * @dataProvider provideMediaTypesNotInRange
     */
    public function isMediaTypeNotInRangeOf($mediaType, $checkedMediaType)
    {
        $descriptor = MediaTypeDescriptor::parse($mediaType);
        $checkedDescriptor = MediaTypeDescriptor::parse($checkedMediaType);

        $this->assertFalse($descriptor->inRange($checkedDescriptor));
    }

    public function provideMediaTypesInRange()
    {
        yield ['application/xml', 'application/xml'];
        yield ['text/*', 'text/json'];
        yield ['*/*', 'image/png'];
        yield ['application/*', 'application/*'];
        yield ['*/*', '*/*'];
    }

    public function provideMediaTypesNotInRange()
    {
        yield ['application/xml', 'application/json'];
        yield ['text/*', 'application/*'];
    }

    public function provideInvalidInputs()
    {
        yield ['application/json/more'];
        yield ['application/'];
        yield ['text'];
        yield ['/'];
    }
}
