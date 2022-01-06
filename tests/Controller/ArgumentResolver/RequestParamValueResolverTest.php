<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\RequestParam;
use Jungi\FrameworkExtraBundle\Attribute\NamedValue;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestParamValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
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

    protected function createArgumentValueResolver(ConverterInterface $converter): ArgumentValueResolverInterface
    {
        return new RequestParamValueResolver($converter);
    }

    protected function createRequestWithParameters(array $parameters): Request
    {
        return new Request([], $parameters);
    }

    protected function createAttribute(string $name): NamedValue
    {
        return new RequestParam($name);
    }
}
