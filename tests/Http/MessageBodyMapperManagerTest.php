<?php

namespace Jungi\FrameworkExtraBundle\Tests\Http;

use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Http\UnsupportedMediaTypeException;
use Jungi\FrameworkExtraBundle\Mapper\MapperInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class MessageBodyMapperManagerTest extends TestCase
{
    /** @test */
    public function supportedMediaTypes()
    {
        $manager = new MessageBodyMapperManager($this->createDefaultServiceLocator());
        $this->assertEquals(['application/json', 'application/xml'], $manager->getSupportedMediaTypes());
    }

    /** @test */
    public function convertFromMessageBody()
    {
        $type = 'stdClass';
        $jsonData = '{"hello": "json-world"}';
        $xmlData = '<message-body><hello>xml-world</hello></message-body>';

        $expectedJsonObject = new \stdClass();
        $expectedJsonObject->hello = 'json-world';
        $expectedXmlObject = new \stdClass();
        $expectedXmlObject->hello = 'xml-world';

        $manager = new MessageBodyMapperManager(new ServiceLocator(array(
            'application/json' => function () use ($jsonData, $type, $expectedJsonObject) {
                $mock = $this->createMock(MapperInterface::class);
                $mock
                    ->expects($this->once())
                    ->method('mapFrom')
                    ->with($jsonData, $type)
                    ->willReturn($expectedJsonObject);

                return $mock;
            },
            'application/xml' => function () use ($xmlData, $type, $expectedXmlObject) {
                $mock = $this->createMock(MapperInterface::class);
                $mock
                    ->expects($this->once())
                    ->method('mapFrom')
                    ->with($xmlData, $type)
                    ->willReturn($expectedXmlObject);

                return $mock;
            },
        )));

        $this->assertEquals($expectedJsonObject, $manager->mapFrom($jsonData, 'application/json', $type), 'JSON');
        $this->assertEquals($expectedXmlObject, $manager->mapFrom($xmlData, 'application/xml', $type), 'XML');
    }

    /** @test */
    public function convertFromMessageBodyOnNonSupportedMediaType()
    {
        $this->expectException(UnsupportedMediaTypeException::class);

        $manager = new MessageBodyMapperManager($this->createDefaultServiceLocator());
        $manager->mapFrom('foo', 'text/csv', 'stdClass');
    }

    /** @test */
    public function convertToMessageBody()
    {
        $expectedJsonData = '{"hello": "json-world"}';
        $expectedXmlData = '<message-body><hello>xml-world</hello></message-body>';

        $jsonObject = new \stdClass();
        $jsonObject->hello = 'json-world';
        $xmlObject = new \stdClass();
        $xmlObject->hello = 'xml-world';

        $manager = new MessageBodyMapperManager(new ServiceLocator(array(
            'application/json' => function () use ($jsonObject, $expectedJsonData) {
                $mock = $this->createMock(MapperInterface::class);
                $mock
                    ->expects($this->once())
                    ->method('mapTo')
                    ->with($jsonObject)
                    ->willReturn($expectedJsonData);

                return $mock;
            },
            'application/xml' => function () use ($xmlObject, $expectedXmlData) {
                $mock = $this->createMock(MapperInterface::class);
                $mock
                    ->expects($this->once())
                    ->method('mapTo')
                    ->with($xmlObject)
                    ->willReturn($expectedXmlData);

                return $mock;
            },
        )));

        $this->assertEquals($expectedJsonData, $manager->mapTo($jsonObject, 'application/json'), 'JSON');
        $this->assertEquals($expectedXmlData, $manager->mapTo($xmlObject, 'application/xml'), 'XML');
    }

    /** @test */
    public function convertToMessageBodyOnNonSupportedMediaType()
    {
        $this->expectException(UnsupportedMediaTypeException::class);

        $manager = new MessageBodyMapperManager($this->createDefaultServiceLocator());
        $manager->mapTo(new \stdClass(), 'text/csv');
    }

    private function createDefaultServiceLocator(): ServiceLocator
    {
        return new ServiceLocator(array(
            'application/json' => function () { return $this->createMock(MapperInterface::class); },
            'application/xml' => function () { return $this->createMock(MapperInterface::class); },
        ));
    }
}
