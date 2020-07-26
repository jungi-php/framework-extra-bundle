<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Jungi\FrameworkExtraBundle\Annotation\NamedValueArgumentInterface;
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
    public function uploadedFileArgument()
    {
        $annotationLocator = new ServiceLocator(array(
            'FooController$file' => function() {
                return $this->createAnnotationContainer([$this->createAnnotation('file')]);
            }
        ));

        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createArgumentValueResolver($converter, $annotationLocator);
        $request = new Request([], [], ['_controller' => 'FooController'], [], [
            'file' => new UploadedFile(__DIR__.'/../../Fixtures/uploaded_file', 'uploaded_file', 'text/plain'),
        ]);

        $resolver->resolve($request, new ArgumentMetadata('file', UploadedFile::class, false, false, null))->current();
    }

    protected function createArgumentValueResolver(ConverterInterface $converter, ContainerInterface $annotationLocator): ArgumentValueResolverInterface
    {
        return new RequestParamValueResolver($converter, $annotationLocator);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        return new Request([], $parameters);
    }

    protected function createAnnotation(string $name): NamedValueArgumentInterface
    {
        return new RequestParam(array('value' => $name));
    }
}
