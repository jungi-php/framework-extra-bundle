<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Jungi\FrameworkExtraBundle\Attribute\RequestBody as BaseRequestBody;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class RequestBody extends BaseRequestBody implements Argument
{
    use StatefulTrait;

    private $argument;

    public function __construct(array $data)
    {
        parent::__construct($data['type'] ?? null);

        $this->argument = $data['argument'] ?? $data['value'] ?? null;
    }

    public function argument(): string
    {
        return $this->argument;
    }
}
