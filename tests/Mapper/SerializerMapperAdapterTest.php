<?php

namespace Jungi\FrameworkExtraBundle\Tests\Mapper;

use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Mapper\SerializerMapperAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
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
        $this->assertEquals($expected, $mapper->mapFrom($message, Foo::class));
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
        $mapper->mapFrom($message, Foo::class);
    }

    /**
     * @test
     * @group client_error
     */
    public function mapFromDataWithInvalidParameterType()
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
        $mapper->mapFrom($message, Foo::class);
    }

    /**
     * @test
     * @group client_error
     */
    public function mapFromDataWithInvalidParameterTypeWithDisabledTypeEnforcement()
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

        $mapper = $this->createXmlSerializerMapperAdapter(true, ['disable_type_enforcement' => true]);
        $mapper->mapFrom($message, Foo::class);
    }

    /**
     * @test
     * @group client_error
     */
    public function mapFromDataOnMissingConstructorArgument()
    {
        $this->expectException(MalformedDataException::class);
        $message = <<< 'EOXML'
<?xml version="1.0"?>
<message>
    <other>foo</other>
</message>
EOXML;

        $mapper = $this->createXmlSerializerMapperAdapter(true);
        $mapper->mapFrom($message, Foo::class);
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
        $mapper->mapFrom($message, Foo::class);
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
    public function mapToData()
    {
        $mapper = new SerializerMapperAdapter('json', new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]));
        $this->assertJsonStringEqualsJsonString('{"hello": "json-world"}', $mapper->mapTo(new Foo('json-world')));
    }

    /**
     * @test
     * @group server_error
     */
    public function mapToDataOnNonRegisteredNormalizer()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapper = new SerializerMapperAdapter('json', new Serializer([new CustomNormalizer()], [new JsonEncoder()]));
        $mapper->mapTo(new Foo('json-world'));
    }

    /**
     * @test
     */
    public function mapToDataWhenNormalizationIsNotNeeded()
    {
        $mapper = new SerializerMapperAdapter('json', new Serializer([], [new JsonEncoder()]));
        $this->assertJsonStringEqualsJsonString('{"hello": "json-world"}', $mapper->mapTo(array('hello' => 'json-world')));
    }

    private function createXmlSerializerMapperAdapter(bool $propertyInfoEnabled = false, array $context = [])
    {
        $propertyInfo = null;

        if ($propertyInfoEnabled) {
            $extractors = [new ReflectionExtractor()];
            $propertyInfo = new PropertyInfoExtractor($extractors, $extractors, $extractors, $extractors, $extractors);
        }

        return new SerializerMapperAdapter(
            'xml',
            new Serializer([new GetSetMethodNormalizer(null, null, $propertyInfo)], [new XmlEncoder()]),
            $context
        );
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
