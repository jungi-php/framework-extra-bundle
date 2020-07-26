<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures;

use Jungi\FrameworkExtraBundle\Annotation\ArgumentInterface;

final class FakeArgumentAnnotation implements ArgumentInterface
{
    private $name;

    public function __construct(array $data = array())
    {
        $this->name = $data['value'] ?? 'fake';
    }

    public function getArgumentName(): string
    {
        return $this->name;
    }
}
