<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Jungi\FrameworkExtraBundle\Attribute\QueryParams as BaseQueryParams;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class QueryParams extends BaseQueryParams implements Argument
{
    use StatefulTrait;

    private $argument;

    public function __construct(array $data)
    {
        $this->argument = $data['argument'] ?? $data['value'] ?? null;
    }

    public function argument(): string
    {
        return $this->argument;
    }
}
