<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\ClassMethodAnnotationRegistry;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestBodyValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\FakeArgumentAnnotation;
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
        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );

        $request = new Request();
        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [new RequestBody(['value' => 'foo'])]));

        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null)));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', 'stdClass', false, false, null)));
        $this->assertFalse($resolver->supports(new Request(), new ArgumentMetadata('foo', 'stdClass', false, false, null)));

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [new FakeArgumentAnnotation()]));

        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null)));
    }

    /**
     * @test
     * @dataProvider provideConvertibleTypes
     */
    public function convertRequestBody(string $argumentType, ?string $annotatedAsType)
    {
        $request = new Request([], [], [], [], [], [], 'hello-world');
        $request->headers->set('Content-Type', 'application/vnd.jungi.test');

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            new RequestBody(['value' => 'foo', 'type' => $annotatedAsType])
        ]));

        $argument = new ArgumentMetadata('foo', $argumentType, false, false, null);

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with($request->getContent(), $annotatedAsType ?: $argumentType);

        $resolver = new RequestBodyValueResolver($this->createMock(MessageBodyMapperManager::class), $converter);
        $resolver->resolve($request, $argument)->current();
    }

    /**
     * @test
     * @dataProvider provideMapableTypes
     */
    public function mapRequestBody(string $argumentType, ?string $annotatedAsType)
    {
        $request = new Request([], [], [], [], [], [], 'hello-world');
        $request->headers->set('Content-Type', 'application/vnd.jungi.test');

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            new RequestBody(['value' => 'foo', 'type' => $annotatedAsType])
        ]));

        $argument = new ArgumentMetadata('foo', $argumentType, false, false, null);

        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $messageBodyMapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->with($request->getContent(), $request->headers->get('CONTENT_TYPE'), $annotatedAsType ?: $argumentType);

        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $this->createMock(ConverterInterface::class));
        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function multipartFormDataRequest()
    {
        $request = new Request([], [
            'hello' => 'world'
        ], [], [], [
            'file' => new UploadedFile(__DIR__.'/../../Fixtures/uploaded_file', 'uploaded_file', 'text/plain'),
        ]);
        $request->headers->set('Content-Type', 'multipart/form-data');

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            new RequestBody(['value' => 'foo'])
        ]));

        $argument = new ArgumentMetadata('foo', 'stdClass', false, false, null);

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->with(RequestUtils::getRequestBodyParameters($request), $argument->getType());

        $resolver = new RequestBodyValueResolver($this->createMock(MessageBodyMapperManager::class), $converter);
        $resolver->resolve($request, $argument)->current();
    }

    /**
     * @test
     * @dataProvider provideRegularFileClassTypes
     */
    public function regularFileRequest($type)
    {
        $request = new Request([], [], [], [], [], [], 'hello,world');
        $request->headers->set('Content-Type', 'text/csv');

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            new RequestBody(['value' => 'foo'])
        ]));

        $argument = new ArgumentMetadata('foo', $type, false, false, null);

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );

        /** @var \SplFileInfo $file */
        $file = $resolver->resolve($request, $argument)->current();

        $this->assertInstanceOf($type, $file);
        $this->assertEquals('hello,world', $file->openFile('r')->fread(32));
    }

    /** @test */
    public function uploadedFileRequest()
    {
        $request = new Request([], [], [], [], [], [], 'hello,world');
        $request->headers->set('Content-Type', 'text/csv');

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            new RequestBody(['value' => 'foo'])
        ]));

        $argument = new ArgumentMetadata('foo', UploadedFile::class, false, false, null);

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );

        /** @var UploadedFile $file */
        $file = $resolver->resolve($request, $argument)->current();

        $this->assertEquals('hello,world', $file->openFile('r')->fread(32));
        $this->assertEquals('text/csv', $file->getClientMimeType());
        $this->assertEmpty($file->getClientOriginalName());
        $this->assertEmpty($file->getClientOriginalExtension());
        $this->assertTrue($file->isValid());
        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
    }

    /** @test */
    public function nullableArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" cannot be nullable');

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', 'string', false, false, null, true))->current();
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
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', null, false, false, null))->current();
    }

    /** @test */
    public function malformedRequestData()
    {
        $this->expectException(BadRequestHttpException::class);

        $messageBodyMapperManager = $this->createMock(MessageBodyMapperManager::class);
        $messageBodyMapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->willThrowException(new MalformedDataException('Malformed data'));

        $request = new Request();
        $request->headers->set('Content-Type', 'application/json');

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            new RequestBody(['value' => 'foo'])
        ]));

        $resolver = new RequestBodyValueResolver($messageBodyMapperManager, $this->createMock(ConverterInterface::class));
        $resolver->resolve($request, new ArgumentMetadata('foo', 'stdClass', false, false, null))->current();
    }

    /** @test */
    public function invalidRequestBodyParameters()
    {
        $this->expectException(BadRequestHttpException::class);

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->willThrowException(new TypeConversionException('Cannot convert data'));

        $request = new Request([], ['foo' => 'bar']);
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            new RequestBody(['value' => 'foo'])
        ]));

        $resolver = new RequestBodyValueResolver($this->createMock(MessageBodyMapperManager::class), $converter);
        $resolver->resolve($request, new ArgumentMetadata('foo', 'stdClass', false, false, null))->current();
    }

    /** @test */
    public function missingRequestContentType()
    {
        $this->expectException(BadRequestHttpException::class);

        $request = new Request();

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            new RequestBody(['value' => 'foo'])
        ]));

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );
        $resolver->resolve($request, new ArgumentMetadata('foo', 'stdClass', false, false, null))->current();
    }

    /** @test */
    public function inappropriateArgumentTypeAnnotation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectDeprecationMessage('Expected argument "foo" to be type hinted as "array"');

        $request = new Request([], [], [], [], [], [], '[{"hello": "world"}, {"hello": "me"}]');
        $request->headers->set('Content-Type', 'application/json');

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            new RequestBody(['value' => 'foo', 'type' => 'stdClass[]'])
        ]));

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class)
        );
        $resolver->resolve($request, new ArgumentMetadata('foo', 'stdClass', false, false, null))->current();
    }

    public function provideRegularFileClassTypes()
    {
        yield [File::class];
        yield ['SplFileInfo'];
        yield ['SplFileObject'];
    }

    public function provideConvertibleTypes()
    {
        yield ['string', null];
        yield ['int', null];
        yield ['float', null];
        yield ['bool', null];
        yield ['array', null];
        yield ['array', 'string[]'];
        yield ['array', 'int[]'];
        yield ['array', 'float[]'];
        yield ['array', 'bool[]'];
        yield ['array', 'string[][]'];
    }

    public function provideMapableTypes()
    {
        yield ['stdClass', null];
        yield ['array', 'stdClass[]'];
        yield ['array', 'stdClass[][]'];
    }
}
