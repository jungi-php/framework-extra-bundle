<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestParamValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestParamValueResolverTest extends AbstractNamedValueArgumentValueResolverTest
{
    /** @test */
    public function uploadedFileArgument()
    {
        $attributeLocator = new ServiceLocator(array(
            'FooController$file' => function() {
                return $this->createAttributeContainer([$this->createAttribute('file')]);
            }
        ));

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createAttributeArgumentValueResolver($converter, $attributeLocator);
        $request = new Request([], [], ['_controller' => 'FooController'], [], [
            'file' => new UploadedFile(__DIR__.'/../../Fixtures/uploaded_file', 'uploaded_file', 'text/plain'),
        ]);

        $resolver->resolve($request, new ArgumentMetadata('file', UploadedFile::class, false, false, null))->current();
    }

    protected function createAttributeArgumentValueResolver(ConverterInterface $converter, ContainerInterface $attributeLocator): ArgumentValueResolverInterface
    {
        return RequestParamValueResolver::onAttribute($converter, $attributeLocator);
    }

    protected function createAnnotationArgumentValueResolver(ConverterInterface $converter, ContainerInterface $attributeLocator): ArgumentValueResolverInterface
    {
        return RequestParamValueResolver::onAnnotation($converter, $attributeLocator);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        return new Request([], $parameters);
    }

    protected function createAttribute(string $name): NamedValue
    {
        return new Attribute\RequestParam($name);
    }

    protected function createAnnotation(string $name): NamedValue
    {
        return new Annotation\RequestParam(['name' => $name]);
    }
}
