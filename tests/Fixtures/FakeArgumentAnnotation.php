<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures;

use Jungi\FrameworkExtraBundle\Annotation\ArgumentAnnotationInterface;

final class FakeArgumentAnnotation implements ArgumentAnnotationInterface
{
    private $name;

    public function __construct(array $data = array())
    {
        $this->name = $data['value'] ?? 'fake';
    }

    public function getName(): string
    {
        return $this->name;
    }
}
