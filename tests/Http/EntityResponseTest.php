<?php

namespace Jungi\FrameworkExtraBundle\Tests\Http;

use Jungi\FrameworkExtraBundle\Http\EntityResponse;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Http\NotAcceptableMediaTypeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class EntityResponseTest extends TestCase
{
    /** @test */
    public function entityResponse()
    {
        $response = new EntityResponse('foo', 201, ['Foo' => 'bar']);

        $this->assertEquals('foo', $response->getEntity());
        $this->assertSame('', $response->getContent());
        $this->assertSame('bar', $response->headers->get('Foo'));
        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function entityIsAltered()
    {
        $response = new EntityResponse('foo', 201, ['Foo' => 'bar']);
        $response->setContent('"foo"');

        $response->setEntity('bar');

        $this->assertEquals('bar', $response->getEntity());
        $this->assertEquals('', $response->getContent());
    }

    /** @test */
    public function prepareDoesNothing()
    {
        $request = new Request();
        $request->setRequestFormat('json');
        $response = new EntityResponse('foo', 200);

        $response->prepare($request);

        $this->assertFalse($response->headers->has('Content-Type'));
    }

    /** @test */
    public function entityIsMappedToRequestFormat()
    {
        $request = new Request();
        $request->setRequestFormat('json');

        $this->assertThatContentIsNegotiatedFor($request, 'application/json', 'application/xml');
    }

    /** @test */
    public function entityIsMappedToDefaultContentType()
    {
        $this->assertThatContentIsNegotiatedFor(new Request(), 'application/json', 'application/json');
    }

    /** @test */
    public function entityIsMappedToDefaultContentTypeOnInvalidRequestFormat()
    {
        $request = new Request();
        $request->setRequestFormat('invalid');

        $this->assertThatContentIsNegotiatedFor($request, 'application/xml', 'application/xml');
    }

    /** @test */
    public function entityIsMappedToDefaultContentTypeOnInvalidAcceptableMediaType()
    {
        $request = new Request();
        $request->headers->set('Accept', '*/xml');

        $this->assertThatContentIsNegotiatedFor($request, 'application/json', 'application/json');
    }

    /**
     * @test
     * @dataProvider provideAcceptableMediaTypes
     */
    public function entityIsMappedToAcceptableMediaType(string $expectedContentType, string $acceptedMediaTypes, string $defaultContentType)
    {
        $request = new Request();
        $request->headers->set('Accept', $acceptedMediaTypes);

        $this->assertThatContentIsNegotiatedFor($request, $expectedContentType, $defaultContentType);
    }

    /** @test */
    public function entityMappingFailsOnNoRegisteredMessageBodyMappers()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('You need to register at least one message body mapper');

        $response = new EntityResponse('foo');
        $response->negotiateContent(new Request(), new MessageBodyMapperManager(new ServiceLocator([])), 'application/json');
    }

    /** @test */
    public function entityMappingFailsOnNotAcceptableMediaType()
    {
        $this->expectException(NotAcceptableMediaTypeException::class);

        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $messageBodyMapperManager
            ->method('getSupportedMediaTypes')
            ->willReturn(['application/json', 'application/vnd.jungi.test']);

        $request = new Request();
        $request->setRequestFormat('xml');

        $response = new EntityResponse('foo');
        $response->negotiateContent($request, $messageBodyMapperManager, 'application/json');
    }

    public function provideAcceptableMediaTypes()
    {
        yield 'without a quality value' => [
            'application/xml',
            'application/xml, application/vnd.jungi.test',
            'application/json'
        ];
        yield 'all with a quality value' => [
            'application/xml',
            'application/vnd.jungi.test;q=0.1, application/xml;q=0.8',
            'application/json',
        ];
        yield 'some with a quality value' => [
            'application/vnd.jungi.test',
            'application/json;q=0.5, application/vnd.jungi.test, application/xml;q=0.8',
            'application/json',
        ];
        yield 'empty' => ['application/json', '', 'application/json'];
        yield 'wildcard' => ['application/json', '*/*', 'application/json'];
    }

    private function assertThatContentIsNegotiatedFor(Request $request, string $expectedContentType, string $defaultContentType)
    {
        $entity = 'raw-entity';
        $responseBody = 'converted-entity';

        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $messageBodyMapperManager
            ->expects($this->once())
            ->method('mapTo')
            ->with($entity, $expectedContentType)
            ->willReturn($responseBody);
        $messageBodyMapperManager
            ->expects($this->once())
            ->method('getSupportedMediaTypes')
            ->willReturn(['application/json', 'application/xml', 'application/vnd.jungi.test']);

        $response = new EntityResponse($entity);
        $response->negotiateContent($request, $messageBodyMapperManager, $defaultContentType);

        $this->assertEquals($expectedContentType, $response->headers->get('Content-Type'));
        $this->assertEquals($responseBody, $response->getContent());
    }
}

