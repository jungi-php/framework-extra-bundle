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

    /**
     * @test
     * @dataProvider provideUnsupported
     */
    public function unsupported($type)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Unsupported type "%s"', $type));

        $converter = new BuiltinTypeSafeConverter();
        $converter->convert(123, $type);
    }

    /** @test */
    public function nonNumericValuePhpNotice()
    {
        $converter = new BuiltinTypeSafeConverter();

        try {
            $converter->convert('123foo', 'int');
        } catch (TypeConversionException $e) {
            // continue
        }

        $this->assertEquals(123, $converter->convert('123', 'int'));
    }

    public function provideValid()
    {
        yield [123, '123', 'int'];
        yield ['1.23', 1.23, 'string'];
        yield [1.23, '1.23', 'float'];
        yield [true, 1, 'bool'];
        yield [false, 0, 'bool'];
    }

    public function provideInvalid()
    {
        yield ['foo', 'int'];
        yield ['foo', 'float'];
        yield ['123foo', 'int'];
        yield ['foo123', 'int'];
        yield ['foo1.23', 'float'];
        yield ['1.23foo', 'float'];
    }

    public function provideUnsupported()
    {
        yield ['object'];
        yield ['array'];
    }
}
