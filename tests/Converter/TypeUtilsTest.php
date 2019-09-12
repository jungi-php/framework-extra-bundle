<?php

namespace Jungi\FrameworkExtraBundle\Tests\Converter;

use Jungi\FrameworkExtraBundle\Converter\TypeUtils;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class TypeUtilsTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideValuesOfBuiltInType
     */
    public function isValueOfBuiltInType($value, string $type)
    {
        $this->assertTrue(TypeUtils::isValueOfType($value, $type));

        foreach ($this->provideValuesOfBuiltInType() as list (, $notPassingType)) {
            if ($notPassingType === $type) {
                continue;
            }

            $this->assertFalse(TypeUtils::isValueOfType($value, $notPassingType));
        }
    }

    /** @test */
    public function isValueOfObjectType()
    {
        $this->assertTrue(TypeUtils::isValueOfType(new \InvalidArgumentException(), \InvalidArgumentException::class), 'A is B');
        $this->assertTrue(TypeUtils::isValueOfType(new \InvalidArgumentException(), \LogicException::class), 'A extends B');
        $this->assertTrue(TypeUtils::isValueOfType(new \InvalidArgumentException(), \Throwable::class), 'A implements B');
        $this->assertFalse(TypeUtils::isValueOfType(new \InvalidArgumentException(), \RuntimeException::class), 'A does not extend and is not B');
    }

    /** @test */
    public function isValueOfUndefinedType()
    {
        $this->expectException(\InvalidArgumentException::class);

        TypeUtils::isValueOfType('123', 'invalid');
    }

    public function provideValuesOfBuiltInType()
    {
        yield ['foo', 'string'];
        yield [123, 'int'];
        yield [1.23, 'float'];
        yield [true, 'bool'];
        yield [array(), 'array'];
        yield [new \stdClass(), 'object'];
    }
}
