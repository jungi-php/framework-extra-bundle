<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\RequestHeader;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestHeaderValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestHeaderValueResolverTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideArgumentValues
     */
    public function argumentValueIsResolved(string $type, mixed $value)
    {
        $converter = $this->createMock(ConverterInterface::class);
        $converter
            ->expects($this->once())
            ->method('convert')
            ->willReturnArgument(0);

        $resolver = new RequestHeaderValueResolver($converter);

        $request = new Request();
        $request->headers->set('foo', $value);

        $argument = new ArgumentMetadata('foo', $type, false, false, null, false, [
            new RequestHeader('foo')
        ]);

        $this->assertSame($value, $resolver->resolve($request, $argument)->current());
    }

    public function provideArgumentValues(): iterable
    {
        yield ['string', 'bar'];
        yield ['array', ['multi', 'bar']];
    }
}
