<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\RequestCookie;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\RequestCookieValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestCookieValueResolverTest extends TestCase
{
    /** @test */
    public function argumentValueIsResolved()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new RequestCookieValueResolver($converter);

        $request = new Request([], [], [], [
            'foo' => 'bar'
        ]);
        $argument = new ArgumentMetadata('foo', null, false, false, null, false, [
            new RequestCookie()
        ]);

        $this->assertEquals('bar', $resolver->resolve($request, $argument)->current());
    }
}
