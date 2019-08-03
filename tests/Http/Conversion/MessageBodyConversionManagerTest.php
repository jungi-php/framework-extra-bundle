<?php

namespace Jungi\FrameworkExtraBundle\Tests\Http\Conversion;

use Jungi\FrameworkExtraBundle\Http\Conversion\Mapper\MapperInterface;
use Jungi\FrameworkExtraBundle\Http\Conversion\MessageBodyConversionManager;
use Jungi\FrameworkExtraBundle\Http\UnsupportedMediaTypeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

class MessageBodyConversionManagerTest extends TestCase
{
    /** @test */
    public function supportedMediaTypes()
    {
        $conversionManager = new MessageBodyConversionManager($this->createDefaultServiceLocatorMock());
        $this->assertEquals(['application/json', 'application/xml'], $conversionManager->getSupportedMediaTypes());
    }

    /** @test */
    public function convertFromInputMessage()
    {
        $type = 'stdClass';
        $jsonData = '{"hello": "json-world"}';
        $xmlData = '<message-body><hello>xml-world</hello></message-body>';

        $expectedJsonObject = new \stdClass();
        $expectedJsonObject->hello = 'json-world';
        $expectedXmlObject = new \stdClass();
        $expectedXmlObject->hello = 'xml-world';

        $conversionManager = new MessageBodyConversionManager(new ServiceLocator(array(
            'application/json' => function () use ($jsonData, $type, $expectedJsonObject) {
                $mock = $this->createMock(MapperInterface::class);
                $mock
                    ->expects($this->once())
                    ->method('mapFromData')
                    ->with($jsonData, $type)
                    ->willReturn($expectedJsonObject);

                return $mock;
            },
            'application/xml' => function () use ($xmlData, $type, $expectedXmlObject) {
                $mock = $this->createMock(MapperInterface::class);
                $mock
                    ->expects($this->once())
                    ->method('mapFromData')
                    ->with($xmlData, $type)
                    ->willReturn($expectedXmlObject);

                return $mock;
            },
        )));

        $this->assertEquals($expectedJsonObject, $conversionManager->convertFromInputMessage($jsonData, $type, 'application/json'), 'JSON');
        $this->assertEquals($expectedXmlObject, $conversionManager->convertFromInputMessage($xmlData, $type, 'application/xml'), 'XML');
    }

    /** @test */
    public function convertFromInputMessageOnNonSupportedMediaType()
    {
        $this->expectException(UnsupportedMediaTypeException::class);

        $conversionManager = new MessageBodyConversionManager($this->createDefaultServiceLocatorMock());
        $conversionManager->convertFromInputMessage('foo', 'stdClass', 'text/csv');
    }

    /** @test */
    public function convertToOutputMessage()
    {
        $expectedJsonData = '{"hello": "json-world"}';
        $expectedXmlData = '<message-body><hello>xml-world</hello></message-body>';

        $jsonObject = new \stdClass();
        $jsonObject->hello = 'json-world';
        $xmlObject = new \stdClass();
        $xmlObject->hello = 'xml-world';

        $conversionManager = new MessageBodyConversionManager(new ServiceLocator(array(
            'application/json' => function () use ($jsonObject, $expectedJsonData) {
                $mock = $this->createMock(MapperInterface::class);
                $mock
                    ->expects($this->once())
                    ->method('mapDataTo')
                    ->with($jsonObject)
                    ->willReturn($expectedJsonData);

                return $mock;
            },
            'application/xml' => function () use ($xmlObject, $expectedXmlData) {
                $mock = $this->createMock(MapperInterface::class);
                $mock
                    ->expects($this->once())
                    ->method('mapDataTo')
                    ->with($xmlObject)
                    ->willReturn($expectedXmlData);

                return $mock;
            },
        )));

        $this->assertEquals($expectedJsonData, $conversionManager->convertToOutputMessage($jsonObject, 'application/json'), 'JSON');
        $this->assertEquals($expectedXmlData, $conversionManager->convertToOutputMessage($xmlObject, 'application/xml'), 'XML');
    }

    /** @test */
    public function convertToOutputMessageOnNonSupportedMediaType()
    {
        $this->expectException(UnsupportedMediaTypeException::class);

        $conversionManager = new MessageBodyConversionManager($this->createDefaultServiceLocatorMock());
        $conversionManager->convertToOutputMessage(new \stdClass(), 'text/csv');
    }

    private function createDefaultServiceLocatorMock(): ServiceLocator
    {
        return new ServiceLocator(array(
            'application/json' => function () { return $this->createMock(MapperInterface::class); },
            'application/xml' => function () { return $this->createMock(MapperInterface::class); },
        ));
    }
}
