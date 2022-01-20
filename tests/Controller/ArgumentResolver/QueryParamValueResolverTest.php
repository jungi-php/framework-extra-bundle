<?php

namespace Jungi\FrameworkExtraBundle\Tests\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\QueryParam;
use Jungi\FrameworkExtraBundle\Controller\ArgumentResolver\QueryParamValueResolver;
use Jungi\FrameworkExtraBundle\Converter\ConverterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class QueryParamValueResolverTest extends TestCase
{
    /** @test */
    public function argumentValueIsResolved()
    {
        $converter = $this->createMock(ConverterInterface::class);
        $resolver = new QueryParamValueResolver($converter);

        $request = new Request([
            'foo' => 'bar'
        ]);
        $argument = new ArgumentMetadata('foo', null, false, false, null, false, [
            new QueryParam()
        ]);

        $this->assertEquals('bar', $resolver->resolve($request, $argument)->current());
    }
}
