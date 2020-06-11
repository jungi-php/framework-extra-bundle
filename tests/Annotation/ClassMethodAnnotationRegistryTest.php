<?php

namespace Jungi\FrameworkExtraBundle\Tests\Annotation;

use Jungi\FrameworkExtraBundle\Annotation\ClassMethodAnnotationRegistry;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\Tests\Fixtures\FakeArgumentAnnotation;
use PHPUnit\Framework\TestCase;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
class ClassMethodAnnotationRegistryTest extends TestCase
{
    /** @test */
    public function create()
    {
        $classAnnotation = new ResponseBody();
        $methodAnnotation = new ResponseBody();
        $dataArgAnnotation = new RequestBody(array('value' => 'data'));
        $queryDataAnnotationQueryParam = new QueryParam(array('value' => 'queryData'));
        $queryDataAnnotationFake = new FakeArgumentAnnotation(array('value' => 'queryData'));

        $registry = new ClassMethodAnnotationRegistry([$classAnnotation], [$methodAnnotation], [
            $dataArgAnnotation,
            $queryDataAnnotationQueryParam,
            $queryDataAnnotationFake,
        ]);

        $this->assertSame([$classAnnotation], $registry->getClassAnnotations());
        $this->assertSame([$methodAnnotation], $registry->getMethodAnnotations());
        $this->assertSame([$dataArgAnnotation], $registry->getArgumentAnnotations('data'));
        $this->assertSame([
            $queryDataAnnotationQueryParam,
            $queryDataAnnotationFake,
        ], $registry->getArgumentAnnotations('queryData'));
        $this->assertSame($classAnnotation, $registry->getClassAnnotation(ResponseBody::class));
        $this->assertSame($methodAnnotation, $registry->getMethodAnnotation(ResponseBody::class));
        $this->assertSame($dataArgAnnotation, $registry->getArgumentAnnotation('data', RequestBody::class));
        $this->assertTrue($registry->hasClassAnnotation(ResponseBody::class));
        $this->assertFalse($registry->hasClassAnnotation(QueryParam::class));
        $this->assertTrue($registry->hasMethodAnnotation(ResponseBody::class));
        $this->assertFalse($registry->hasMethodAnnotation(QueryParam::class));
        $this->assertTrue($registry->hasArgumentAnnotation('queryData', QueryParam::class));
        $this->assertFalse($registry->hasArgumentAnnotation('queryData', RequestBody::class));

        try {
            $registry->getArgumentAnnotation('data', FakeArgumentAnnotation::class);
            $this->fail('Expected OutOfBoundsException');
        } catch (\OutOfBoundsException $e) {
            $this->assertTrue(true);
        }

        try {
            $registry->getArgumentAnnotations('invalid');
            $this->fail('Expected OutOfBoundsException');
        } catch (\OutOfBoundsException $e) {
            $this->assertTrue(true);
        }

        try {
            $registry->getMethodAnnotation(FakeArgumentAnnotation::class);
            $this->fail('Expected OutOfBoundsException');
        } catch (\OutOfBoundsException $e) {
            $this->assertTrue(true);
        }

        try {
            $registry->getClassAnnotation(FakeArgumentAnnotation::class);
            $this->fail('Expected OutOfBoundsException');
        } catch (\OutOfBoundsException $e) {
            $this->assertTrue(true);
        }
    }
}
