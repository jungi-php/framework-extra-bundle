<?php

namespace Jungi\FrameworkExtraBundle\Tests\Mapper;

use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Mapper\ConverterMapperAdapter;
use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ConverterMapperAdapterTest extends TestCase
{
    /** @test */
    public function mapFrom(): void
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with('123', 'int')
            ->willReturn(123);

        $mapper = new ConverterMapperAdapter('string', $converter);
        $mapper->mapFrom('123', 'int');
    }

    /** @test */
    public function mapFromMalformedData(): void
    {
        $this->expectException(MalformedDataException::class);

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->willThrowException(new TypeConversionException());

        $mapper = new ConverterMapperAdapter('string', $converter);
        $mapper->mapFrom('foo', 'int');
    }

    /** @test */
    public function mapTo(): void
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with(123, 'string')
            ->willReturn('123');

        $mapper = new ConverterMapperAdapter('string', $converter);
        $mapper->mapTo(123);
    }
}
