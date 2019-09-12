<?php

namespace Jungi\FrameworkExtraBundle\Tests\Converter;

use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\ConverterManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ConverterManagerTest extends TestCase
{
    /** @test */
    public function dataIsAlreadyOfType()
    {
        $manager = new ConverterManager(new ServiceLocator([]));
        $data = 123;

        $this->assertSame($data, $manager->convert($data, 'int'));
    }

    /** @test */
    public function converterForRegisteredType()
    {
        $manager = new ConverterManager(new ServiceLocator(array(
            'int' => function () {
                $converter = $this->createMock(ConverterInterface::class);
                $converter
                    ->expects($this->once())
                    ->method('convert')
                    ->with('123', 'int');

                return $converter;
            },
        )));

        $manager->convert('123', 'int');
    }

    /** @test */
    public function converterForNonRegisteredType()
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = new ConverterManager(new ServiceLocator([]));
        $manager->convert('123', 'int');
    }

    /** @test */
    public function converterForNonRegisteredObjectType()
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = new ConverterManager(new ServiceLocator([]));
        $manager->convert('1992-12-10 23:23:23', \DateTimeImmutable::class);
    }

    /** @test */
    public function conversionToBuiltInObjectTypeIsNotSupported()
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = new ConverterManager(new ServiceLocator([]));
        $manager->convert('123', 'object');
    }

    /** @test */
    public function objectConverterIsUsedForNonRegisteredObjectType()
    {
        $manager = new ConverterManager(new ServiceLocator(array(
            'object' => function () {
                $converter = $this->createMock(ConverterInterface::class);
                $converter
                    ->expects($this->once())
                    ->method('convert')
                    ->with('1992-12-10 23:23:23', \DateTime::class);

                return $converter;
            },
        )));
        $manager->convert('1992-12-10 23:23:23', \DateTime::class);
    }
}
