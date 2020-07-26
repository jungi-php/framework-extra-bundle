<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class QueryParams extends AbstractAnnotation implements ArgumentInterface
{
    /**
     * @Required
     *
     * @var string
     */
    private $argumentName;

    public function __construct(array $data)
    {
        $this->argumentName = $data['argumentName'] ?? $data['argument'] ?? $data['value'] ?? null;
    }

    public function getArgumentName(): string
    {
        return $this->argumentName;
    }
}
