<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestBodyValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Converter\TypeConversionException;
use Jungi\FrameworkExtraBundle\Http\MessageBodyMapperManager;
use Jungi\FrameworkExtraBundle\Mapper\MalformedDataException;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\DummyObject;
use Jungi\FrameworkExtraBundle\Tests\TestCase;
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
        // Attribute
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\RequestBody()]);
            }
        ));
        $resolver = RequestBodyValueResolver::onAttribute(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $attributeLocator
        );

        $request = new Request([], [], array('_controller' => 'FooController'));
        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null)));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', 'stdClass', false, false, null)));

        $request = new Request([], [], array('_controller' => 'BarController'));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null)));

        // Dummy attribute
        $attributeLocator = new ServiceLocator(array(
            'BarController$foo' => function() {
                return $this->createAttributeContainer([new DummyObject()]);
            }
        ));
        $resolver = RequestBodyValueResolver::onAttribute(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $attributeLocator
        );
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null)));

        // Annotation
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Annotation\RequestBody(['value' => 'foo'])]);
            }
        ));
        $resolver = RequestBodyValueResolver::onAnnotation(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $attributeLocator
        );
        $request = new Request([], [], array('_controller' => 'FooController'));
        $this->assertTrue($resolver->supports($request, new ArgumentMetadata('foo', 'stdClass', false, false, null)));
        $this->assertFalse($resolver->supports($request, new ArgumentMetadata('bar', null, false, false, null)));
    }

    /**
     * @test
     * @dataProvider provideMapableTypes
     */
    public function mapRequestBody(string $argumentType, ?string $annotatedAsType)
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() use ($annotatedAsType) {
                return $this->createAttributeContainer([new Attribute\RequestBody($annotatedAsType)]);
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

        $resolver = RequestBodyValueResolver::onAttribute($mapperManager, $converter, $attributeLocator);
        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function multipartFormDataRequest()
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\RequestBody()]);
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

        $resolver = RequestBodyValueResolver::onAttribute($mapperManager, $converter, $attributeLocator);
        $resolver->resolve($request, $argument)->current();
    }

    /**
     * @test
     * @dataProvider provideRegularFileClassTypes
     */
    public function regularFileRequest($type)
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\RequestBody()]);
            }
        ));

        $request = new Request([], [], ['_controller' => 'FooController'], [], [], [], 'hello,world');
        $request->headers->set('Content-Type', 'text/csv');

        $argument = new ArgumentMetadata('foo', $type, false, false, null);

        $resolver = RequestBodyValueResolver::onAttribute(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $attributeLocator
        );

        /** @var \SplFileInfo $file */
        $file = $resolver->resolve($request, $argument)->current();

        $this->assertInstanceOf($type, $file);
        $this->assertEquals('hello,world', $file->openFile('r')->fread(32));
    }

    /** @test */
    public function uploadedFileRequest()
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\RequestBody()]);
            }
        ));

        $request = new Request([], [], ['_controller' => 'FooController'], [], [], [], 'hello,world');
        $request->headers->set('Content-Type', 'text/csv');
        $request->headers->set('Content-Disposition', 'inline; filename = "foo123.csv"');

        $argument = new ArgumentMetadata('foo', UploadedFile::class, false, false, null);

        $resolver = RequestBodyValueResolver::onAttribute(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $attributeLocator
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
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\RequestBody()]);
            }
        ));

        $request = new Request([], [], ['_controller' => 'FooController'], [], [], [], '123');
        $argument = new ArgumentMetadata('foo', 'int', false, false, null);

        $converter = $this->createMock(ConverterInterface::class);
        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $mapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->with('123', 'application/vnd.jungi.test', 'int');

        $resolver = RequestBodyValueResolver::onAttribute($mapperManager, $converter, $attributeLocator, 'application/vnd.jungi.test');
        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function argumentIsSetToNullOnEmptyBodyAndUnavailableContentType()
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\RequestBody()]);
            }
        ));

        $request = new Request([], [], ['_controller' => 'FooController']);
        $argument = new ArgumentMetadata('foo', 'string', false, false, null, true);

        $resolver = RequestBodyValueResolver::onAttribute(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $attributeLocator
        );

        $this->assertNull($resolver->resolve($request, $argument)->current());
    }

    /** @test */
    public function mapperIsUsedOnEmptyBodyAndAvailableContentType()
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\RequestBody()]);
            }
        ));

        $request = new Request([], [], ['_controller' => 'FooController']);
        $request->headers->set('Content-Type', 'application/vnd.jungi.test');

        $argument = new ArgumentMetadata('foo', 'string', false, false, null, true);

        $mapperManager = $this->createMock(MessageBodyMapperManager::class);
        $mapperManager
            ->expects($this->once())
            ->method('mapFrom')
            ->with('', 'application/vnd.jungi.test', 'string');

        $resolver = RequestBodyValueResolver::onAttribute(
            $mapperManager,
            $this->createMock(ConverterInterface::class),
            $attributeLocator
        );
        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function notNullableArgumentOnEmptyBody()
    {
        $this->expectException(BadRequestHttpException::class);

        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\RequestBody()]);
            }
        ));

        $request = new Request([], [], ['_controller' => 'FooController']);
        $argument = new ArgumentMetadata('foo', 'string', false, false, null, false);

        $resolver = RequestBodyValueResolver::onAttribute(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $attributeLocator
        );
        $resolver->resolve($request, $argument)->current();
    }

    /** @test */
    public function defaultArgumentValueIsUsed()
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\RequestBody()]);
            }
        ));

        $request = new Request([], [], ['_controller' => 'FooController']);
        $argument = new ArgumentMetadata('foo', 'int', false, true, 123, false);

        $resolver = RequestBodyValueResolver::onAttribute(
            $this->createMock(MessageBodyMapperManager::class),
            $this->createMock(ConverterInterface::class),
            $attributeLocator
        );

        $this->assertEquals(123, $resolver->resolve($request, $argument)->current());
    }

    /** @test */
    public function argumentWithoutType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument "foo" must have the type specified');

        $resolver = RequestBodyValueResolver::onAttribute(
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

        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\RequestBody()]);
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
        
        $resolver = RequestBodyValueResolver::onAttribute($mapperManager, $converter, $attributeLocator);
        $resolver->resolve($request, new ArgumentMetadata('foo', 'stdClass', false, false, null))->current();
    }

    /** @test */
    public function invalidRequestBodyParameters()
    {
        $this->expectException(BadRequestHttpException::class);

        $attributeLocator = new ServiceLocator(array(
            'FooController$foo' => function() {
                return $this->createAttributeContainer([new Attribute\RequestBody()]);
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

        $resolver = RequestBodyValueResolver::onAttribute($mapperManager, $converter, $attributeLocator);
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
