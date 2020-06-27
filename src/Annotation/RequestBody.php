<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Jungi\FrameworkExtraBundle\Utils\TypeUtils;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestBody extends AbstractAnnotation implements ArgumentAnnotationInterface
{
    /**
     * @Required
     *
     * @var string
     */
    private $argumentName;

    /**
     * @var string|null
     */
    private $argumentType;

    public function __construct(array $data)
    {
        $this->argumentName = $data['argumentName'] ?? $data['value'] ?? null;

        if ($type = $data['argumentType'] ?? $data['type'] ?? null) {
            $this->setArgumentType($type);
        }
    }

    public function getArgumentName(): string
    {
        return $this->argumentName;
    }

    public function getArgumentType(): ?string
    {
        return $this->argumentType;
    }

    private function setArgumentType(string $type): void
    {
        if (!TypeUtils::isCollection($type)) {
            throw new \InvalidArgumentException('Argument type can be specified only for a collection.');
        }

        $this->argumentType = $type;
    }
}
