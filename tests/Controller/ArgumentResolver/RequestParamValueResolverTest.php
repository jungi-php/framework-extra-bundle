<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation;
use Jungi\FrameworkExtraBundle\Attribute;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestParamValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
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
    use ExpectDeprecationTrait;

    /** @test */
    public function uploadedFileArgument()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createArgumentValueResolver($converter);
        $request = new Request([], [], ['_controller' => 'FooController'], [], [
            'file' => new UploadedFile(__DIR__.'/../../Fixtures/uploaded_file', 'uploaded_file', 'text/plain'),
        ]);

        $resolver->resolve($request, new ArgumentMetadata('file', UploadedFile::class, false, false, null, false, [
            $this->createAttribute('file')
        ]))->current();
    }

    /**
     * @test
     * @group legacy
     */
    public function deprecationOnAnnotation(): void
    {
        $this->expectDeprecation(sprintf('Since jungi/framework-extra-bundle 1.4: The "%s::%s" method is deprecated, use the constructor instead.', RequestParamValueResolver::class, 'onAnnotation'));

        RequestParamValueResolver::onAnnotation($this->createMock(ConverterInterface::class), new ServiceLocator([]));
    }

    /**
     * @test
     * @group legacy
     */
    public function deprecationOnAttribute(): void
    {
        $this->expectDeprecation(sprintf('Since jungi/framework-extra-bundle 1.4: The "%s::%s" method is deprecated, use the constructor instead.', RequestParamValueResolver::class, 'onAttribute'));

        RequestParamValueResolver::onAttribute($this->createMock(ConverterInterface::class), new ServiceLocator([]));
    }

    protected function createArgumentValueResolver(ConverterInterface $converter, ?ContainerInterface $attributeLocator = null): ArgumentValueResolverInterface
    {
        return new RequestParamValueResolver($converter, $attributeLocator);
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
