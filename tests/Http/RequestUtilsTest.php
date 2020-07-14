<?php

namespace Jungi\FrameworkExtraBundle\Tests\Http;

use Jungi\FrameworkExtraBundle\Http\RequestUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class RequestUtilsTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideValidControllers
     */
    public function validControllerCallableStrings(string $expectedSyntax, $controller): void
    {
        $request = new Request([], [], ['_controller' => $controller]);

        $this->assertEquals($expectedSyntax, RequestUtils::getControllerAsCallableString($request));
    }

    /**
     * @test
     * @dataProvider provideInvalidControllers
     */
    public function invalidControllerCallableStrings($controller): void
    {
        $request = new Request([], [], ['_controller' => $controller]);

        $this->assertNull(RequestUtils::getControllerAsCallableString($request));
    }

    public function provideValidControllers()
    {
        yield ['foo::method', ['\foo', 'method']];
        yield ['foo::method', ['foo', 'method']];
        yield ['foo::method', '\foo::method'];
        yield ['foo::method', 'foo::method'];
        yield ['foo', 'foo'];
    }

    public function provideInvalidControllers()
    {
        yield [[new \stdClass(), 'action']];
        yield [new \stdClass()];
        yield [function() {}];
    }
}
