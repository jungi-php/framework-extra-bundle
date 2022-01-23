<?php

namespace Jungi\FrameworkExtraBundle\Tests\Converter;

use Jungi\FrameworkExtraBundle\Converter\SerializerObjectConverterAdapter;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Serializer\ObjectAlreadyOfTypeDenormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class SerializerObjectConverterAdapterTest extends TestCase
{
    /** @test */
    public function convertToSameType()
    {
        $converter = new SerializerObjectConverterAdapter(new DateTimeNormalizer());

        $expected = new \DateTimeImmutable('1992-12-10 23:22:21');
        $actual = $converter->convert(new \DateTimeImmutable('1992-12-10 23:22:21'), \DateTimeImmutable::class);

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function convertStringToDate()
    {
        $converter = new SerializerObjectConverterAdapter(new DateTimeNormalizer());

        $expected = new \DateTimeImmutable('1992-12-10 23:22:21');
        $actual = $converter->convert('1992-12-10 23:22:21', \DateTimeImmutable::class);

        $this->assertEquals($expected, $actual);

        $expected = new \DateTime('1992-12-10 23:22:21');
        $actual = $converter->convert('1992-12-10 23:22:21', \DateTime::class);

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function convertArrayToDto()
    {
        $extractors = [new ReflectionExtractor()];
        $propertyInfo = new PropertyInfoExtractor($extractors, $extractors, $extractors, $extractors, $extractors);
        $converter = new SerializerObjectConverterAdapter(new Serializer([new PropertyNormalizer(null, null, $propertyInfo)]));

        $expected = new FooDto('hello-world', true, new Number(123), array('foo' => 'bar'));
        $actual = $converter->convert(array(
            'stringVal' => 'hello-world',
            'boolVal' => true,
            'numberVal' => array('value' => 123),
            'arrayVal' => array('foo' => 'bar'),
        ), FooDto::class);

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function convertStringArrayToDto()
    {
        $extractors = [new ReflectionExtractor()];
        $propertyInfo = new PropertyInfoExtractor($extractors, $extractors, $extractors, $extractors, $extractors);
        $converter = new SerializerObjectConverterAdapter(
            new Serializer([new PropertyNormalizer(null, null, $propertyInfo)]),
            ['disable_type_enforcement' => true]
        );

        $expected = new FooDto('hello-world', true, new Number(123), array('foo' => 'bar'));
        $actual = $converter->convert(array(
            'stringVal' => 'hello-world',
            'boolVal' => '1',
            'numberVal' => array('value' => '123'),
            'arrayVal' => array('foo' => 'bar'),
        ), FooDto::class);

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function convertPartiallyDenormalizedArrayToDto()
    {
        $extractors = [new ReflectionExtractor()];
        $propertyInfo = new PropertyInfoExtractor($extractors, $extractors, $extractors, $extractors, $extractors);
        $converter = new SerializerObjectConverterAdapter(
            new Serializer([
                new ObjectAlreadyOfTypeDenormalizer(),
                new PropertyNormalizer(null, null, $propertyInfo)
            ]),
            ['disable_type_enforcement' => true]
        );

        $expected = new FooDto('hello-world', true, new Number(123), array('foo' => 'bar'));
        $actual = $converter->convert(array(
            'stringVal' => 'hello-world',
            'boolVal' => '1',
            'numberVal' => new Number(123), // @see RequestBodyValueResolver: multipart/form-data file support
            'arrayVal' => array('foo' => 'bar'),
        ), FooDto::class);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     *
     * Unfortunately the type safety is automatically abandoned when using the option "disable_type_enforcement".
     * Request parameters as well data decoded by XmlEncoder comes as a string, so the type casting is required
     * to properly convert it.
     */
    public function convertArrayWithNonCastableTypeToDto()
    {
        $this->expectException(TypeConversionException::class);

        $extractors = [new ReflectionExtractor()];
        $propertyInfo = new PropertyInfoExtractor($extractors, $extractors, $extractors, $extractors, $extractors);
        $converter = new SerializerObjectConverterAdapter(new Serializer(
            [new PropertyNormalizer(null, null, $propertyInfo)]),
            ['disable_type_enforcement' => true]
        );

        $converter->convert(array(
            'stringVal' => 'hello-world',
            'boolVal' => '1',
            'numberVal' => array('value' => 'invalid'), // string -> int (uncastable) = TypeError
            'arrayVal' => array('num' => '1'),
        ), FooDto::class);
    }

    /** @test */
    public function convertInvalidStringFormatToDate()
    {
        $this->expectException(TypeConversionException::class);

        $converter = new SerializerObjectConverterAdapter(new DateTimeNormalizer(), ['datetime_format' => '!Y-m-d']);

        $converter->convert('10-12-1992', \DateTimeImmutable::class);
    }
}

class Number
{
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }
}

class FooDto
{
    private $stringVal;
    private $boolVal;
    private $numberVal;
    private $arrayVal;

    public function __construct(string $stringVal, bool $boolVal, Number $numberVal, array $arrayVal)
    {
        $this->stringVal = $stringVal;
        $this->boolVal = $boolVal;
        $this->numberVal = $numberVal;
        $this->arrayVal = $arrayVal;
    }

    public function getStringVal(): string
    {
        return $this->stringVal;
    }

    public function getBoolVal(): bool
    {
        return $this->boolVal;
    }

    public function getNumberVal(): Number
    {
        return $this->numberVal;
    }

    public function getArrayVal(): array
    {
        return $this->arrayVal;
    }
}
