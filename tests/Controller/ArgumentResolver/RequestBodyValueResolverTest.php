<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestBodyValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\DependencyInjection\SimpleContainer;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\FakeArgumentAnnotation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
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
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return new SimpleContainer(array(
                    RequestBody::class => new RequestBody(['value' => 'foo'])
                ));
            }
        ));
        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $annotationLocator
        );

        $request = new Request([], [], array('_controller' => 'FooController'));
        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null)));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', 'stdClass', false, false, null)));

        $request = new Request([], [], array('_controller' => 'BarController'));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null)));

        $annotationLocator = new ServiceLocator(array(
            'BarController$foo' => function() {
                return new SimpleContainer(array(
                    FakeArgumentAnnotation::class => new FakeArgumentAnnotation(['value' => 'foo'])
                ));
            }
        ));
        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $annotationLocator
        );
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null)));
    }

    /**
     * @test
     * @dataProvider provideMapableTypes
     */
    public function mapRequestBody(string $argumentType, ?string $annotatedAsType)
    {
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() use ($annotatedAsType) {
                return new SimpleContainer(array(
                    RequestBody::class => new RequestBody(['value' => 'foo', 'type' => $annotatedAsType])
                ));
            }
        ));

        $request = new Request([], [], ['_controller' => 'FooController'], [], [], [], 'hello-world');
        $request->headers->set('Content-Type', 'application/vnd.jungi.test');

        $argument = new ArgumentMetadata('foo', $argumentType, false, false, null);

        $converter = $this->createMock(ConverterInterface::class);
        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $mapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->with('hello-world', 'application/vnd.jungi.test', $annotatedAsType ?: $argumentType);

        $resolver = new RequestBodyValueResolver($mapperManager, $converter, $annotationLocator);
        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function multipartFormDataRequest()
    {
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return new SimpleContainer(array(
                    RequestBody::class => new RequestBody(['value' => 'foo'])
                ));
            }
        ));

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

        $argument = new ArgumentMetadata('foo', 'stdClass', false, false, null);
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
            ->with($expectedData, $argument->getType());

        $resolver = new RequestBodyValueResolver($mapperManager, $converter, $annotationLocator);
        $resolver->resolve($request, $argument)->current();
    }

    /**
     * @test
     * @dataProvider provideRegularFileClassTypes
     */
    public function regularFileRequest($type)
    {
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return new SimpleContainer(array(
                    RequestBody::class => new RequestBody(['value' => 'foo'])
                ));
            }
        ));

        $request = new Request([], [], ['_controller' => 'FooController'], [], [], [], 'hello,world');
        $request->headers->set('Content-Type', 'text/csv');

        $argument = new ArgumentMetadata('foo', $type, false, false, null);

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $annotationLocator
        );

        /** @var \SplFileInfo $file */
        $file = $resolver->resolve($request, $argument)->current();

        $this->assertInstanceOf($type, $file);
        $this->assertEquals('hello,world', $file->openFile('r')->fread(32));
    }

    /** @test */
    public function uploadedFileRequest()
    {
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return new SimpleContainer(array(
                    RequestBody::class => new RequestBody(['value' => 'foo'])
                ));
            }
        ));

        $request = new Request([], [], ['_controller' => 'FooController'], [], [], [], 'hello,world');
        $request->headers->set('Content-Type', 'text/csv');
        $request->headers->set('Content-Disposition', 'inline; filename = "foo123.csv"');

        $argument = new ArgumentMetadata('foo', UploadedFile::class, false, false, null);

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $annotationLocator
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
        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return new SimpleContainer(array(
                    RequestBody::class => new RequestBody(['value' => 'foo'])
                ));
            }
        ));

        $request = new Request([], [], ['_controller' => 'FooController'], [], [], [], '123');
        $argument = new ArgumentMetadata('foo', 'int', false, false, null);

        $converter = $this->createMock(ConverterInterface::class);
        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $mapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->with('123', 'text/plain', 'int');

        $resolver = new RequestBodyValueResolver($mapperManager, $converter, $annotationLocator);
        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function nullableArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" cannot be nullable');

        $resolver = new RequestBodyValueResolver(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            new ServiceLocator([])
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
            $this->createMock(ConverterInterface::class),
            new ServiceLocator([])
        );
        $resolver->resolve(new Request(), new ArgumentMetadata('foo', null, false, false, null))->current();
    }

    /** @test */
    public function malformedRequestData()
    {
        $this->expectException(BadRequestHttpException::class);

        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return new SimpleContainer(array(
                    RequestBody::class => new RequestBody(['value' => 'foo'])
                ));
            }
        ));

        $converter = $this->createMock(ConverterInterface::class);
        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $mapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->willThrowException(new MalformedDataException('Malformed data'));

        $request = new Request([], [], array('_controller' => 'FooController'));
        $request->headers->set('Content-Type', 'application/json');
        
        $resolver = new RequestBodyValueResolver($mapperManager, $converter, $annotationLocator);
        $resolver->resolve($request, new ArgumentMetadata('foo', 'stdClass', false, false, null))->current();
    }

    /** @test */
    public function invalidRequestBodyParameters()
    {
        $this->expectException(BadRequestHttpException::class);

        $annotationLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return new SimpleContainer(array(
                    RequestBody::class => new RequestBody(['value' => 'foo'])
                ));
            }
        ));

        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->willThrowException(new TypeConversionException('Cannot convert data'));

        $request = new Request([], ['foo' => 'bar'], ['_controller' => 'FooController']);
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');

        $resolver = new RequestBodyValueResolver($mapperManager, $converter, $annotationLocator);
        $resolver->resolve($request, new ArgumentMetadata('foo', 'stdClass', false, false, null))->current();
    }

    public function provideRegularFileClassTypes()
    {
        yield [File::class];
        yield ['SplFileInfo'];
        yield ['SplFileObject'];
    }

    public function provideMapableTypes()
    {
        yield ['string', null];
        yield ['stdClass', null];
        yield ['array', 'stdClass[]'];
        yield ['array', 'stdClass[][]'];
        yield ['array', 'string[]'];
        yield ['array', 'string[][]'];
    }
}
