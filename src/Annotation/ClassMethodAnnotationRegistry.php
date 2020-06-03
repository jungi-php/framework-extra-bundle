<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

/**
 * @internal
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class ClassMethodAnnotationRegistry
{
    /**
     * @var object[]
     */
    private $classAnnotations;

    /**
     * @var object[]
     */
    private $methodAnnotations;

    /**
     * @var ArgumentAnnotationInterface[][]
     */
    private $argumentAnnotations;

    /**
     * @param object[]                      $classAnnotations
     * @param object[]                      $methodAnnotations
     * @param ArgumentAnnotationInterface[] $argumentAnnotations
     */
    public function __construct(array $classAnnotations, array $methodAnnotations, array $argumentAnnotations)
    {
        $this->setClassAnnotations($classAnnotations);
        $this->setMethodAnnotations($methodAnnotations);
        $this->setArgumentAnnotations($argumentAnnotations);
    }

    public function hasClassAnnotation(string $annotationClass): bool
    {
        return isset($this->classAnnotations[$annotationClass]);
    }

    public function getClassAnnotation(string $annotationClass): object
    {
        if (!isset($this->classAnnotations[$annotationClass])) {
            throw new \OutOfBoundsException(sprintf('Annotation "%s" does not exist.', $annotationClass));
        }

        return $this->classAnnotations[$annotationClass];
    }

    public function getClassAnnotations(): array
    {
        return array_values($this->classAnnotations);
    }

    public function hasMethodAnnotation(string $annotationClass): bool
    {
        return isset($this->methodAnnotations[$annotationClass]);
    }

    public function getMethodAnnotation(string $annotationClass): object
    {
        if (!isset($this->methodAnnotations[$annotationClass])) {
            throw new \OutOfBoundsException(sprintf('Annotation "%s" does not exist.', $annotationClass));
        }

        return $this->methodAnnotations[$annotationClass];
    }

    public function getMethodAnnotations(): array
    {
        return array_values($this->methodAnnotations);
    }

    public function hasArgumentAnnotation(string $name, string $annotationClass): bool
    {
        return isset($this->argumentAnnotations[$name][$annotationClass]);
    }

    public function getArgumentAnnotation(string $name, string $annotationClass): ArgumentAnnotationInterface
    {
        if (!isset($this->argumentAnnotations[$name][$annotationClass])) {
            throw new \OutOfBoundsException(sprintf('Annotation "%s" does not exist for argument "%s".', $annotationClass, $name));
        }

        return $this->argumentAnnotations[$name][$annotationClass];
    }

    /** @return ArgumentAnnotationInterface[] */
    public function getArgumentAnnotations(string $name): array
    {
        if (!isset($this->argumentAnnotations[$name])) {
            throw new \OutOfBoundsException(sprintf('Argument "%s" does not exist.', $name));
        }

        return array_values($this->argumentAnnotations[$name]);
    }

    private function setClassAnnotations(array $annotations): void
    {
        $this->classAnnotations = [];
        foreach ($annotations as $annotation) {
            $this->classAnnotations[get_class($annotation)] = $annotation;
        }
    }

    private function setMethodAnnotations(array $annotations): void
    {
        $this->methodAnnotations = [];
        foreach ($annotations as $annotation) {
            $this->methodAnnotations[get_class($annotation)] = $annotation;
        }
    }

    /**
     * @param ArgumentAnnotationInterface[] $annotations
     */
    private function setArgumentAnnotations(array $annotations): void
    {
        $this->argumentAnnotations = [];
        foreach ($annotations as $annotation) {
            if (!isset($this->argumentAnnotations[$annotation->getArgumentName()])) {
                $this->argumentAnnotations[$annotation->getArgumentName()] = [];
            }

            $this->argumentAnnotations[$annotation->getArgumentName()][get_class($annotation)] = $annotation;
        }
    }
}
