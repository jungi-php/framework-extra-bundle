<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller;

use Jungi\FrameworkExtraBundle\Http\ResponseFactory;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\FooController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Mapping\ClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ControllerTest extends TestCase
{
    /** @test */
    public function entity()
    {
        $request = new Request();
        $entity = array('hello' => 'world');
        $status = 201;
        $headers = ['Custom' => 'foo'];

        $responseFactory = $this->createMock(ResponseFactory::class);
        $responseFactory
            ->expects($this->once())
            ->method('createEntityResponse')
            ->with($request, $entity, $status, $headers);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $container = new Container();
        $container->set('request_stack', $requestStack);
        $container->set('jungi.response_factory', $responseFactory);

        $controller = new FooController();
        $controller->setContainer($container);

        $controller->withEntity($entity, $status, $headers);
    }

    /** @test */
    public function normalizedEntity()
    {
        $request = new Request();
        $normalizedEntity = array('hello' => 'world');
        $status = 201;
        $headers = ['Custom' => 'foo'];

        $responseFactory = $this->createMock(ResponseFactory::class);
        $responseFactory
            ->expects($this->once())
            ->method('createEntityResponse')
            ->with($request, $normalizedEntity, $status, $headers);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $container = new Container();
        $container->set('request_stack', $requestStack);
        $container->set('jungi.response_factory', $responseFactory);
        $container->set('serializer.normalizer', $this->createNormalizerWithGroupAttribute());

        $controller = new FooController();
        $controller->setContainer($container);

        $controller->withNormalizedEntity(new FooData(), ['groups' => 'foo'], $status, $headers);
    }

    private function createNormalizerWithGroupAttribute(): PropertyNormalizer
    {
        $attribute = new AttributeMetadata('hello');
        $attribute->addGroup('foo');
        $classMetadata = new ClassMetadata(FooData::class);
        $classMetadata->addAttributeMetadata($attribute);

        $classMetadataFactory = $this->createMock(ClassMetadataFactory::class);
        $classMetadataFactory
            ->method('getMetadataFor')
            ->willReturn($classMetadata);

        return new PropertyNormalizer($classMetadataFactory);
    }
}

class FooData
{
    public $hello = 'world';
    public $sensitive = 'ups';
}
