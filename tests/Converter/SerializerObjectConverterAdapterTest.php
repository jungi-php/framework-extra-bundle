<?php

namespace Jungi\FrameworkExtraBundle\Tests\Converter;

use Jungi\FrameworkExtraBundle\Converter\SerializerObjectConverterAdapter;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class SerializerObjectConverterAdapterTest extends TestCase
{
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
    public function convertArrayToDTO()
    {
        $extractors = [new ReflectionExtractor()];
        $propertyInfo = new PropertyInfoExtractor($extractors, $extractors, $extractors, $extractors, $extractors);
        $converter = new SerializerObjectConverterAdapter(new GetSetMethodNormalizer(null, null, $propertyInfo));

        $expected = new FooDTO('hello-world', true, false, array('num' => 1));
        $actual = $converter->convert(array(
            'foo' => 'hello-world',
            'boolTrue' => true,
            'boolFalse' => false,
            'collection' => array('num' => 1),
        ), FooDTO::class);

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function convertStringArrayToDTO()
    {
        $extractors = [new ReflectionExtractor()];
        $propertyInfo = new PropertyInfoExtractor($extractors, $extractors, $extractors, $extractors, $extractors);
        $converter = new SerializerObjectConverterAdapter(new GetSetMethodNormalizer(null, null, $propertyInfo), ['disable_type_enforcement' => true]);

        $expected = new FooDTO('hello-world', true, false, array('num' => 1));
        $actual = $converter->convert(array(
            'foo' => 'hello-world',
            'boolTrue' => '1',
            'boolFalse' => '0',
            'collection' => array('num' => '1'),
        ), FooDTO::class);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     *
     * Unfortunately the type safety is automatically abandoned when using the option "disable_type_enforcement".
     * Request parameters as well data decoded by XmlEncoder comes as a string, so the type casting is required
     * to properly convert it.
     */
    public function convertArrayWithNonCastableTypeToDTO()
    {
        $this->expectException(TypeConversionException::class);

        $extractors = [new ReflectionExtractor()];
        $propertyInfo = new PropertyInfoExtractor($extractors, $extractors, $extractors, $extractors, $extractors);
        $converter = new SerializerObjectConverterAdapter(new GetSetMethodNormalizer(null, null, $propertyInfo), ['disable_type_enforcement' => true]);

        $expected = new FooDTO('hello-world', true, false, array('num' => 1));
        $actual = $converter->convert(array(
            'foo' => 'hello-world',
            'boolTrue' => '1',
            'boolFalse' => array('invalid'), // array -> bool = TypeError
            'collection' => array('num' => '1'),
        ), FooDTO::class);

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function convertInvalidStringFormatToDate()
    {
        $this->expectException(TypeConversionException::class);

        $converter = new SerializerObjectConverterAdapter(new DateTimeNormalizer(), ['datetime_format' => '!Y-m-d']);

        $converter->convert('10-12-1992', \DateTimeImmutable::class);
    }
}

class FooDTO
{
    private $foo;
    private $boolTrue;
    private $boolFalse;
    private $collection;

    /**
     * @param string $foo
     * @param bool   $boolTrue
     * @param bool   $boolFalse
     * @param array  $collection
     */
    public function __construct(string $foo, bool $boolTrue, bool $boolFalse, array $collection)
    {
        $this->foo = $foo;
        $this->boolTrue = $boolTrue;
        $this->boolFalse = $boolFalse;
        $this->collection = $collection;
    }

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function getBoolTrue(): bool
    {
        return $this->boolTrue;
    }

    public function getBoolFalse(): bool
    {
        return $this->boolFalse;
    }

    public function getCollection(): array
    {
        return $this->collection;
    }
}
