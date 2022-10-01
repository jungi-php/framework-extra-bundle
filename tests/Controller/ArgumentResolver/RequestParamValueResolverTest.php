<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\RequestParam;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestParamValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestParamValueResolverTest extends TestCase
{
    /** @test */
    public function argumentValueIsResolved()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new RequestParamValueResolver($converter);

        $argument = new ArgumentMetadata('foo', null, false, false, null, false, [
            new RequestParam()
        ]);
        $request = new Request([], [
            'foo' => 'bar'
        ]);

        $values = $resolver->resolve($request, $argument);

        $this->assertCount(1, $values);
        $this->assertEquals('bar', $values[0]);
    }

    /** @test */
    public function argumentValueIsResolvedToUploadedFile()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new RequestParamValueResolver($converter);

        $argument = new ArgumentMetadata('foo', null, false, false, null, false, [
            new RequestParam()
        ]);
        $expected = new UploadedFile(__DIR__.'/../../Fixtures/uploaded_file', 'uploaded_file', 'text/plain');
        $request = new Request([], [], [], [], [
            'foo' => $expected
        ]);

        $values = $resolver->resolve($request, $argument);
        
        $this->assertCount(1, $values);
        $this->assertSame($expected, $values[0]);
    }
}
