<?php

namespace Jungi\FrameworkExtraBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Jungi\FrameworkExtraBundle\Util\TypeUtils;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestBody implements ArgumentAnnotationInterface
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
        if (isset($data['value'])) {
            $this->argumentName = $data['value'];
        }
        if (isset($data['type'])) {
            $this->setArgumentType($data['type']);
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
