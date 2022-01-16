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

        // Attribute
        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $converter);

        $request = new Request([], [], array('_controller' => 'FooController'));
        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null, false, [
            new RequestBody()
        ])));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null, false, [
            new ForeignAttribute()
        ])));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', 'stdClass', false, false, null)));
    }

    /**
     * @test
     * @dataProvider provideMappableTypes
     */
    public function mapRequestBody(string $argumentType, ?string $annotatedAsType)
    {
        $request = new Request([], [], ['_controller' => 'FooController'], [], [], [], 'hello-world');
        $request->headers->set('Content-Type', 'application/vnd.jungi.test');

        $converter = $this->createMock(ConverterInterface::class);
        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $mapperManager
            ->expects($this->exactly(1))
            ->method('mapFrom')
            ->with('hello-world', 'application/vnd.jungi.test', $annotatedAsType ?: $argumentType);

        $resolver = new RequestBodyValueResolver($mapperManager, $converter);
        $resolver->resolve($request, new ArgumentMetadata('foo', $argumentType, false, false, null, false, [
            new RequestBody($annotatedAsType)
        ]))->current();
    }

    /** @test */
    public function multipartFormDataRequest()
    {
        $file = new UploadedFile(__DIR__.'/../../Fixtures/uploaded_file', 'uploaded_file', 'text/plain');
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

        $expectedData = array(
            'hello' => 'world',
            'attachments' => [array(
                'name' => 'foo',
                'file' => $file,
            )],
        );

        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with($expectedData, 'stdClass');

        $resolver = new RequestBodyValueResolver($mapperManager, $converter);
        $resolver->resolve($request, new ArgumentMetadata('foo', 'stdClass', false, false, null, false, [
            new RequestBody()
        ]))->current();
    }

    /**
     * @test
     * @dataProvider provideRegularFileClassTypes
     */
    public function regularFileRequest($type)
    {
        $request = new Request([], [], ['_controller' => 'FooController'], [], [], [], 'hello,world');
        $request->headers->set('Content-Type', 'text/csv');

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );

        /** @var \SplFileInfo $file */
        $file = $resolver->resolve($request, new ArgumentMetadata('foo', $type, false, false, null, false, [
            new RequestBody()
        ]))->current();

        $this->assertInstanceOf($type, $file);
        $this->assertEquals('hello,world', $file->openFile('r')->fread(32));
    }

    /** @test */
    public function uploadedFileRequest()
    {
        $request = new Request([], [], ['_controller' => 'FooController'], [], [], [], 'hello,world');
        $request->headers->set('Content-Type', 'text/csv');
        $request->headers->set('Content-Disposition', 'inline; filename = "foo123.csv"');

        $argument = new ArgumentMetadata('foo', UploadedFile::class, false, false, null, false, [
            new RequestBody()
        ]);

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );

        /** @var UploadedFile $file */
        $file = $resolver->resolve($request, $argument)->current();

        $this->assertEquals('hello,world', $file->openFile('r')->fread(32));
        $this->assertEquals('text/csv', $file->getClientMimeType());
        $this->assertEquals('foo123.csv', $file->getClientOriginalName());
        $this->assertEquals('csv', $file->getClientOriginalExtension());
        $this->assertTrue($file->isValid());
        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());

        $request->headers->set('Content-Disposition', 'attachment; filename = "foo123.csv"');

        /** @var UploadedFile $file */
        $file = $resolver->resolve($request, $argument)->current();

        $this->assertEmpty($file->getClientOriginalName());
        $this->assertEmpty($file->getClientOriginalExtension());
    }

    /** @test */
    public function defaultContentTypeIsUsed()
    {
        $request = new Request([], [], ['_controller' => 'FooController'], [], [], [], '123');

        $converter = $this->createMock(ConverterInterface::class);
        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $mapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->with('123', 'application/vnd.jungi.test', 'int');

        $resolver = new RequestBodyValueResolver($mapperManager, $converter, 'application/vnd.jungi.test');
        $resolver->resolve($request, new ArgumentMetadata('foo', 'int', false, false, null, false, [
            new RequestBody()
        ]))->current();
    }

    /** @test */
    public function argumentIsSetToNullOnEmptyBodyAndUnavailableContentType()
    {
        $request = new Request([], [], ['_controller' => 'FooController']);

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );

        $this->assertNull($resolver->resolve($request, new ArgumentMetadata('foo', 'string', false, false, null, true, [
            new RequestBody()
        ]))->current());
    }

    /** @test */
    public function mapperIsUsedOnEmptyBodyAndAvailableContentType()
    {
        $request = new Request([], [], ['_controller' => 'FooController']);
        $request->headers->set('Content-Type', 'application/vnd.jungi.test');

        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $mapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->with('', 'application/vnd.jungi.test', 'string');

        $resolver = new RequestBodyValueResolver(
            $mapperManager,
            $this->createMock(ConverterInterface::class)
        );
        $resolver->resolve($request, new ArgumentMetadata('foo', 'string', false, false, null, true, [
            new RequestBody()
        ]))->current();
    }

    /** @test */
    public function notNullableArgumentOnEmptyBody()
    {
        $this->expectException(BadRequestHttpException::class);

        $request = new Request([], [], ['_controller' => 'FooController']);

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );
        $resolver->resolve($request, new ArgumentMetadata('foo', 'string', false, false, null, false, [
            new RequestBody()
        ]))->current();
    }

    /** @test */
    public function defaultArgumentValueIsUsed()
    {
        $request = new Request([], [], ['_controller' => 'FooController']);

        $argument = new ArgumentMetadata('foo', 'int', false, true, 123, false, [
            new RequestBody()
        ]);
        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );

        $this->assertEquals(123, $resolver->resolve($request, $argument)->current());
    }

    /** @test */
    public function argumentWithoutType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" must have the type specified');

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', null, false, false, null, false, [
            new RequestBody()
        ]))->current();
    }

    /** @test */
    public function malformedRequestData()
    {
        $this->expectException(BadRequestHttpException::class);

        $converter = $this->createMock(ConverterInterface::class);
        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $mapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->willThrowException(new MalformedDataException('Malformed data'));

        $request = new Request([], [], array('_controller' => 'FooController'));
        $request->headers->set('Content-Type', 'application/json');
        
        $resolver = new RequestBodyValueResolver($mapperManager, $converter);
        $resolver->resolve($request, new ArgumentMetadata('foo', 'stdClass', false, false, null, false, [
            new RequestBody()
        ]))->current();
    }

    /** @test */
    public function invalidRequestBodyParameters()
    {
        $this->expectException(BadRequestHttpException::class);

        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->willThrowException(new TypeConversionException('Cannot convert data'));

        $request = new Request([], ['foo' => 'bar'], ['_controller' => 'FooController']);
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');

        $resolver = new RequestBodyValueResolver($mapperManager, $converter);
        $resolver->resolve($request, new ArgumentMetadata('foo', 'stdClass', false, false, null, false, [
            new RequestBody()
        ]))->current();
    }

    public function provideRegularFileClassTypes()
    {
        yield [File::class];
        yield ['SplFileInfo'];
        yield ['SplFileObject'];
    }

    public function provideMappableTypes()
    {
        yield ['string', null];
        yield ['stdClass', null];
        yield ['array', 'stdClass[]'];
        yield ['array', 'stdClass[][]'];
        yield ['array', 'string[]'];
        yield ['array', 'string[][]'];
    }
}
