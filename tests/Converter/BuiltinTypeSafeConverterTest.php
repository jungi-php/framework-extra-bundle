<?php

namespace Jungi\FrameworkExtraBundle\Tests\Converter;

use Jungi\FrameworkExtraBundle\Converter\BuiltinTypeSafeConverter;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class BuiltinTypeSafeConverterTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideValid
     */
    public function valid($expectedValue, $value, $type)
    {
        $converter = new BuiltinTypeSafeConverter();
        $actualValue = $converter->convert($value, $type);

        $this->assertSame($expectedValue, $actualValue);
    }

    /**
     * @test
     * @dataProvider provideInvalid
     */
    public function invalid($value, $type)
    {
        $this->expectException(TypeConversionException::class);

        $converter = new BuiltinTypeSafeConverter();
        $converter->convert($value, $type);
    }

    public function provideValid()
    {
        yield [123, '123', 'int'];
        yield [123, '123', 'int'];
        yield [1.23, '1.23', 'float'];
        yield [true, 1, 'bool'];
        yield [false, 0, 'bool'];
        yield [1, 1.23, 'int'];
    }

    public function provideInvalid()
    {
        yield ['foo', 'int'];
        yield ['foo', 'float'];
        yield ['123foo', 'int'];
        yield ['foo123', 'int'];
        yield ['foo1.23', 'float'];
        yield ['1.23foo', 'float'];
        yield [[], 'object'];
        yield [new \stdClass(), 'array'];
    }
}
