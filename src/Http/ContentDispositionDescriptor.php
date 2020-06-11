<?php

namespace Jungi\FrameworkExtraBundle\Http;

use Symfony\Component\HttpFoundation\HeaderUtils;

/**
 * @internal
 *
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class ContentDispositionDescriptor
{
    private const TYPE_INLINE = 'inline';

    private $type;
    private $params;

    public static function parse(string $contentDisposition): self
    {
        if (!$contentDisposition) {
            throw new \InvalidArgumentException('Content disposition cannot be empty.');
        }

        $parts = HeaderUtils::split($contentDisposition, ';=');

        $type = array_shift($parts)[0];
        if (!$type) {
            throw new \InvalidArgumentException('Type is not defined.');
        }

        $params = [];
        foreach ($parts as $part) {
            if (!isset($part[0])) {
                throw new \InvalidArgumentException('Encountered on an invalid parameter.');
            }
            if (!isset($part[1])) {
                throw new \InvalidArgumentException(sprintf('Value is missing for the parameter "%s".', $part[0]));
            }

            $params[$part[0]] = $part[1];
        }

        return new self($type, $params);
    }

    public function __construct(string $type, array $params)
    {
        $this->type = $type;
        $this->params = $params;
    }

    public function isInline(): bool
    {
        return self::TYPE_INLINE === $this->type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFilename(): ?string
    {
        return $this->params['filename'] ?? null;
    }

    public function hasParam(string $name): bool
    {
        return isset($this->params[$name]);
    }

    public function getParam(string $name): ?string
    {
        return $this->params[$name] ?? null;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
