<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\RequestBody;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestBodyValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\ForeignAttribute;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestBodyValueResolverTest extends TestCase
{
    /** @test */
    public function supports()
    {
        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);
        $request = new Request();
        
        $argument = new ArgumentMetadata('foo', 'stdClass', false, false, null, false, [
            new RequestBody()
        ]);
        $this->assertTrue($resolver->supports($request, $argument));

        $argument = new ArgumentMetadata('foo', 'stdClass', false, false, null, false, [
            new ForeignAttribute()
        ]);
        $this->assertFalse($resolver->supports($request, $argument));

        $argument = new ArgumentMetadata('bar', 'stdClass', false, false, null);
        $this->assertFalse($resolver->supports($request, $argument));
    }

    /**
     * @test
     * @dataProvider provideMappableTypes
     */
    public function mapRequestBody(string $argumentType, ?string $annotatedAsType, mixed $value)
    {
        $converter = $this->createMock(ConverterInterface::class);
        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $messageBodyMapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->with('hello-world', 'application/vnd.jungi.test', $annotatedAsType ?: $argumentType)
            ->willReturn($value);

        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);

        $request = new Request([], [], [], [], [], [], 'hello-world');
        $request->headers->set('Content-Type', 'application/vnd.jungi.test');

        $argument = new ArgumentMetadata('foo', $argumentType, false, false, null, false, [
            new RequestBody($annotatedAsType)
        ]);
        
        $values = $resolver->resolve($request, $argument);

        $this->assertCount(1, $values);
        $this->assertSame($value, $values[0]);
    }

    /** @test */
    public function multipartFormDataRequest()
    {
        $file = new UploadedFile(__DIR__.'/../../Fixtures/uploaded_file', 'uploaded_file', 'text/plain');
        $expectedData = array(
            'hello' => 'world',
            'attachments' => [array(
                'name' => 'foo',
                'file' => $file,
            )],
        );

        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with($expectedData, 'stdClass')
            ->willReturn(new \stdClass());

        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);

        $request = new Request([], array(
            'hello' => 'world',
            'attachments' => [array('name' => 'foo')],
        ), array(
            '_controller' => 'FooController'
        ), [], array(
            'attachments' => [array(
                'file' => $file
            )],
        ));
        $request->headers->set('Content-Type', 'multipart/form-data');

        $argument = new ArgumentMetadata('foo', 'stdClass', false, false, null, false, [
            new RequestBody()
        ]);

        $values = $resolver->resolve($request, $argument);

        $this->assertCount(1, $values);
        $this->assertInstanceOf('stdClass', $values[0]);
    }

    /**
     * @test
     * @dataProvider provideRegularFileClassTypes
     */
    public function regularFileRequest($type)
    {
        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);

        $request = new Request([], [], [], [], [], [], 'hello,world');
        $request->headers->set('Content-Type', 'text/csv');
        
        $argument = new ArgumentMetadata('foo', $type, false, false, null, false, [
            new RequestBody()
        ]);

        $values = $resolver->resolve($request, $argument);

        $this->assertCount(1, $values);
        $this->assertInstanceOf($type, $values[0]);
        $this->assertEquals('hello,world', $values[0]->openFile('r')->fread(32));
    }

    /** @test */
    public function uploadedFileRequest()
    {
        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);

        $request = new Request([], [], [], [], [], [], 'hello,world');
        $request->headers->set('Content-Type', 'text/csv');
        $request->headers->set('Content-Disposition', 'inline; filename = "foo123.csv"');

        $argument = new ArgumentMetadata('foo', UploadedFile::class, false, false, null, false, [
            new RequestBody()
        ]);

        $values = $resolver->resolve($request, $argument);

        $this->assertCount(1, $values);
        $this->assertEquals('hello,world', $values[0]->openFile('r')->fread(32));
        $this->assertEquals('text/csv', $values[0]->getClientMimeType());
        $this->assertEquals('foo123.csv', $values[0]->getClientOriginalName());
        $this->assertEquals('csv', $values[0]->getClientOriginalExtension());
        $this->assertTrue($values[0]->isValid());
        $this->assertEquals(UPLOAD_ERR_OK, $values[0]->getError());

        $request->headers->set('Content-Disposition', 'attachment; filename = "foo123.csv"');
        $values = $resolver->resolve($request, $argument);

        $this->assertCount(1, $values);
        $this->assertEmpty($values[0]->getClientOriginalName());
        $this->assertEmpty($values[0]->getClientOriginalExtension());
    }

    /** @test */
    public function mapFromDefaultContentTypeOnUnavailableContentType()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $messageBodyMapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->with('123', 'application/vnd.jungi.test', 'int')
            ->willReturn(123);

        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter, 'application/vnd.jungi.test');
        $request = new Request([], [], [], [], [], [], '123');
        $argument = new ArgumentMetadata('foo', 'int', false, false, null, false, [
            new RequestBody()
        ]);
        
        $values = $resolver->resolve($request, $argument);

        $this->assertCount(1, $values);
        $this->assertEquals(123, $values[0]);
    }

    /** @test */
    public function argumentOnEmptyRequestBodyAndUnavailableContentTypeIsResolvedToNullValue()
    {
        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);

        $request = new Request();
        $argument = new ArgumentMetadata('foo', 'string', false, false, null, true, [
            new RequestBody()
        ]);

        $values = $resolver->resolve($request, $argument);

        $this->assertCount(1, $values);
        $this->assertNull($values[0]);
    }

    /** @test */
    public function mapOnEmptyRequestBodyAndAvailableContentType()
    {
        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $messageBodyMapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->with('', 'application/vnd.jungi.test', 'string')
            ->willReturnArgument(0);

        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);

        $request = new Request();
        $request->headers->set('Content-Type', 'application/vnd.jungi.test');
        
        $argument = new ArgumentMetadata('foo', 'string', false, false, null, true, [
            new RequestBody()
        ]);

        $values = $resolver->resolve($request, $argument);

        $this->assertCount(1, $values);
        $this->assertSame('', $values[0]);
    }

    /** @test */
    public function resolveForArgumentWithoutAttributeIsIgnored()
    {
        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $messageBodyMapperManager
            ->expects($this->never())
            ->method('mapFrom');

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);

        $request = new Request();
        $argument = new ArgumentMetadata('foo', 'string', false, false, null, false);

        $this->assertEmpty($resolver->resolve($request, $argument));
    }

    /** @test */
    public function resolveForNotNullableArgumentOnEmptyRequestBodyFails()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Request body cannot be empty');
        
        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);
        
        $request = new Request();
        $argument = new ArgumentMetadata('foo', 'string', false, false, null, false, [
            new RequestBody()
        ]);
        
        $resolver->resolve($request, $argument);
    }

    /** @test */
    public function argumentOnEmptyRequestBodyIsResolvedToDefaultValue()
    {
        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);
        
        $request = new Request();
        $argument = new ArgumentMetadata('foo', 'int', false, true, 123, false, [
            new RequestBody()
        ]);

        $values = $resolver->resolve($request, $argument);

        $this->assertCount(1, $values);
        $this->assertEquals(123, $values[0]);
    }

    /** @test */
    public function resolveForArgumentWithoutTypeFails()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" must have the type specified');

        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);

        $request = new Request();
        $argument = new ArgumentMetadata('foo', null, false, false, null, false, [
            new RequestBody()
        ]);
        
        $resolver->resolve($request, $argument);
    }

    /** @test */
    public function malformedRequestBody()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Request body is malformed');

        $converter = $this->createMock(ConverterInterface::class);
        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $messageBodyMapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->willThrowException(new MalformedDataException('Malformed data'));
        
        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);

        $request = new Request();
        $request->headers->set('Content-Type', 'application/json');
        
        $argument = new ArgumentMetadata('foo', 'stdClass', false, false, null, false, [
            new RequestBody()
        ]);
        
        $resolver->resolve($request, $argument);
    }

    /** @test */
    public function invalidRequestBodyParameters()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Request body parameters are invalid');

        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->willThrowException(new TypeConversionException('Cannot convert data'));

        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);
        
        $request = new Request([], ['foo' => 'bar']);
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');

        $argument = new ArgumentMetadata('foo', 'stdClass', false, false, null, false, [
            new RequestBody()
        ]);
        
        $resolver->resolve($request, $argument);
    }

    public function provideRegularFileClassTypes(): iterable
    {
        yield [File::class];
        yield ['SplFileInfo'];
        yield ['SplFileObject'];
    }

    public function provideMappableTypes(): iterable
    {
        yield ['string', null, 'hello-world'];
        yield ['stdClass', null, new \stdClass()];
        yield ['array', 'stdClass[]', []];
        yield ['array', 'string[]', []];
    }
}
