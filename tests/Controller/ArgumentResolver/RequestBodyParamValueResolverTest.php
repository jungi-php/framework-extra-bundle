<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Annotation\ClassMethodAnnotationRegistry;
use Jungi\FrameworkExtraBundle\Annotation\RequestBodyParam;
use Jungi\FrameworkExtraBundle\Annotation\RequestFieldAnnotationInterface;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestBodyParamValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestBodyParamValueResolverTest extends AbstractRequestFieldValueResolverTest
{
    public function uploadedFileArgument()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->never())
            ->method('convert');

        $resolver = $this->createArgumentValueResolver($converter);
        $request = new Request([], [], [], [], [
            'file' => new UploadedFile(__DIR__.'/../../Fixtures/uploaded_file', 'uploaded_file', 'text/plain'),
        ]);

        RequestUtils::setControllerAnnotationRegistry($request, new ClassMethodAnnotationRegistry([], [], [
            $this->createAnnotation('file'),
        ]));

        $resolver->resolve($request, new ArgumentMetadata('file', UploadedFile::class, false, false, null))->current();
    }

    protected function createArgumentValueResolver(ConverterInterface $converter): ArgumentValueResolverInterface
    {
        return new RequestBodyParamValueResolver($converter);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        return new Request([], $parameters);
    }

    protected function createAnnotation(string $name): RequestFieldAnnotationInterface
    {
        return new RequestBodyParam(array('value' => $name));
    }
}
