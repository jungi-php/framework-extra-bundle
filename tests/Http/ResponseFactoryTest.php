<?php

namespace Jungi\FrameworkExtraBundle\Tests\Http;

use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Http\NotAcceptableMediaTypeException;
use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ResponseFactoryTest extends TestCase
{
    /** @test */
    public function invalidDefaultContentType()
    {
        $this->expectException(\InvalidArgumentException::class);

        new ResponseFactory('application/*', $this->createMock(MessageBodyMapperManager::class));
    }

    /** @test */
    public function entityResponse()
    {
        $responseBody = 'foo';

        $conversionManager = $this->createMock(MessageBodyMapperManager::class);
        $conversionManager
            ->method('mapTo')
            ->willReturn($responseBody);
        $conversionManager
            ->method('getSupportedMediaTypes')
            ->willReturn(['application/json']);

        $factory = new ResponseFactory('application/json', $conversionManager);
        $response = $factory->createEntityResponse(new Request(), 'foo', 201, ['Custom' => 'foo']);

        $this->assertEquals($responseBody, $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($response->headers->has('Custom'));
    }

    /** @test */
    public function entityResponseOnRequestFormat()
    {
        $request = new Request();
        $request->setRequestFormat('json');

        $this->assertEntityResponseFor('application/json', 'application/xml', $request);
    }

    /** @test */
    public function entityResponseWithDefaultContentTypeOnInvalidRequestFormat()
    {
        $request = new Request();
        $request->setRequestFormat('invalid');

        $this->assertEntityResponseFor('application/xml', 'application/xml', $request);
    }

    /**
     * @test
     * @dataProvider provideAcceptableContentTypes
     */
    public function entityResponseOnAcceptableContentTypes($expectedContentType, $contentTypes, $defaultContentType)
    {
        $request = new Request();
        $request->headers->set('Accept', $contentTypes);

        $this->assertEntityResponseFor($expectedContentType, $defaultContentType, $request);
    }

    /** @test */
    public function entityResponseWithDefaultContentTypeOnEmptyAcceptableContentTypes()
    {
        $this->entityResponseOnAcceptableContentTypes('application/xml', '', 'application/xml');
    }

    /** @test */
    public function entityResponseWithDefaultContentTypeOnAcceptableWildcardContentType()
    {
        $this->entityResponseOnAcceptableContentTypes('application/xml', '*/*', 'application/xml');
    }

    /** @test */
    public function entityResponseWithFallbackDefaultContentType()
    {
        $this->assertEntityResponseFor('application/json', 'application/json', new Request());
    }

    /** @test */
    public function entityResponseOnNotSupportedContentType()
    {
        $this->expectException(NotAcceptableMediaTypeException::class);

        $conversionManager = $this->createMock(MessageBodyMapperManager::class);
        $conversionManager
            ->method('getSupportedMediaTypes')
            ->willReturn(['application/json', 'text/csv']);

        $request = new Request();
        $request->setRequestFormat('xml');

        $factory = new ResponseFactory('application/json', $conversionManager);
        $factory->createEntityResponse($request, 'foo');
    }

    /** @test */
    public function entityResponseOnInvalidAcceptableContentType()
    {
        $request = new Request();
        $request->headers->set('Accept', '*/xml');

        $this->assertEntityResponseFor('application/json', 'application/json', $request);
    }

    public function provideAcceptableContentTypes()
    {
        yield ['application/xml', 'application/xml', 'application/json'];
        yield [
            'text/csv',
            'application/json;q=0.5, text/csv, application/xml;q=0.8',
            'application/json',
        ];
        yield [
            'application/xml',
            'text/csv;q=0.1, application/xml;q=0.8',
            'application/json',
        ];
    }

    private function assertEntityResponseFor($expectedContentType, $defaultContentType, Request $request)
    {
        $entity = 'raw-entity';
        $responseBody = 'converted-entity';

        $conversionManager = $this->createMock(MessageBodyMapperManager::class);
        $conversionManager
            ->expects($this->once())
            ->method('mapTo')
            ->with($entity, $expectedContentType)
            ->willReturn($responseBody);
        $conversionManager
            ->expects($this->once())
            ->method('getSupportedMediaTypes')
            ->willReturn(['application/json', 'application/xml', 'text/csv']);

        $factory = new ResponseFactory($defaultContentType, $conversionManager);
        $response = $factory->createEntityResponse($request, $entity);

        $this->assertEquals($responseBody, $response->getContent());
        $this->assertEquals($expectedContentType, $response->headers->get('Content-Type'));
    }
}
