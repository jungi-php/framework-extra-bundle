<?php

namespace Jungi\FrameworkExtraBundle\Tests\Http\Conversion\Mapper;

use Jungi\FrameworkExtraBundle\Http\Conversion\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Http\Conversion\Mapper\SerializerMapperAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerMapperAdapterTest extends TestCase
{
    /** @test */
    public function mapFromData()
    {
        $expected = new Foo('xml-world');
        $message = <<< 'EOXML'
<?xml version="1.0"?>
<message>
    <hello>xml-world</hello>
</message>
EOXML;

        $mapper = $this->createXmlSerializerMapperAdapter($message);
        $this->assertEquals($expected, $mapper->mapFromData($message, Foo::class));
    }

    /**
     * @test
     * @group client_error
     */
    public function mapFromMalformedData()
    {
        $this->expectException(MalformedDataException::class);
        $message = <<< 'EOXML'
<?xml version="1.0"?>
<message><hello></message>
EOXML;

        $mapper = $this->createXmlSerializerMapperAdapter();
        $mapper->mapFromData($message, Foo::class);
    }

    /**
     * @test
     * @group client_error
     */
    public function mapFromDataWithInvalidDataParameterType()
    {
        $this->expectException(MalformedDataException::class);
        $message = <<< 'EOXML'
<?xml version="1.0"?>
<message>
    <hello>
        <array>foo</array>
        <array>bar</array>
    </hello>
</message>
EOXML;

        $mapper = $this->createXmlSerializerMapperAdapter(true);
        $mapper->mapFromData($message, Foo::class);
    }

    /**
     * @test
     * @group server_error
     */
    public function mapFromDataOnNonRegisteredNormalizer()
    {
        $this->expectException(\InvalidArgumentException::class);
        $message = <<< 'EOXML'
<?xml version="1.0"?>
<message>
    <hello>xml-world</hello>
</message>
EOXML;

        $mapper = new SerializerMapperAdapter('xml', new Serializer([new CustomNormalizer()], [new XmlEncoder()]));
        $mapper->mapFromData($message, Foo::class);
    }

    /**
     * @test
     * @group server_error
     */
    public function createWithNotSupportedFormat()
    {
        $this->expectException(\InvalidArgumentException::class);

        new SerializerMapperAdapter('json', new Serializer([new CustomNormalizer()], [new XmlEncoder()]));
    }

    /** @test */
    public function mapDataTo()
    {
        $mapper = new SerializerMapperAdapter('json', new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]));
        $this->assertJsonStringEqualsJsonString('{"hello": "json-world"}', $mapper->mapDataTo(new Foo('json-world')));
    }

    /**
     * @test
     * @group server_error
     */
    public function mapDataToOnNonRegisteredNormalizer()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapper = new SerializerMapperAdapter('json', new Serializer([new CustomNormalizer()], [new JsonEncoder()]));
        $mapper->mapDataTo(new Foo('json-world'));
    }

    /**
     * @test
     */
    public function mapDataToWhenNormalizationIsNotNeeded()
    {
        $mapper = new SerializerMapperAdapter('json', new Serializer([], [new JsonEncoder()]));
        $this->assertJsonStringEqualsJsonString('{"hello": "json-world"}', $mapper->mapDataTo(array('hello' => 'json-world')));
    }

    private function createXmlSerializerMapperAdapter(bool $propertyInfoEnabled = false)
    {
        $propertyInfo = null;

        if ($propertyInfoEnabled) {
            $extractors = [new ReflectionExtractor()];
            $propertyInfo = new PropertyInfoExtractor($extractors, $extractors, $extractors, $extractors, $extractors);
        }

        return new SerializerMapperAdapter('xml', new Serializer([new GetSetMethodNormalizer(null, null, $propertyInfo)], [new XmlEncoder()]));
    }
}

class Foo
{
    private $hello;

    public function __construct(string $hello)
    {
        $this->hello = $hello;
    }

    public function getHello(): string
    {
        return $this->hello;
    }
}
