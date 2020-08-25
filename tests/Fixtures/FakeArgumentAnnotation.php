<?php

namespace Jungi\FrameworkExtraBundle\Tests\Fixtures;

use Jungi\FrameworkExtraBundle\Annotation\Argument;

final class FakeArgumentAnnotation implements Argument
{
    private $name;

    public function __construct(array $data = array())
    {
        $this->name = $data['value'] ?? 'fake';
    }

    public function argument(): string
    {
        return $this->name;
    }
}
