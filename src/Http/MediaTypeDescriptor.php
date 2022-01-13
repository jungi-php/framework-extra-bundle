<?php

namespace Jungi\FrameworkExtraBundle\Http;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 *
 * @internal
 */
final class MediaTypeDescriptor
{
    private const WILDCARD = '*';
    private const SEPARATOR = '/';

    private string $type;
    private string $subType;

    /**
     * @param string[] $mediaTypes
     *
     * @return self[]
     */
    public static function parseList(array $mediaTypes): array
    {
        return array_map(fn (string $mediaType) => self::parse($mediaType), $mediaTypes);
    }

    public static function parse(string $mediaType): self
    {
        if (1 !== substr_count($mediaType, self::SEPARATOR)) {
            throw new \InvalidArgumentException(sprintf('Invalid media type "%s".', $mediaType));
        }

        $parts = explode(self::SEPARATOR, $mediaType);

        return new self($parts[0], $parts[1]);
    }

    public static function parseOrNull(string $mediaType): ?self
    {
        try {
            return self::parse($mediaType);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public static function listToString(array $mediaTypes): array
    {
        return array_map(fn (self $descriptor) => $descriptor->toString(), $mediaTypes);
    }

    public function __construct(string $type, string $subType)
    {
        if (!$type) {
            throw new \InvalidArgumentException('Type cannot be empty.');
        }
        if (!$subType) {
            throw new \InvalidArgumentException('Subtype cannot be empty.');
        }
        if (self::WILDCARD === $type && self::WILDCARD !== $subType) {
            throw new \InvalidArgumentException(sprintf('Invalid media type syntax "%s/%s".', self::WILDCARD, $subType));
        }

        $this->type = $type;
        $this->subType = $subType;
    }

    public function inRange(self $mediaType): bool
    {
        return (self::WILDCARD === $this->type && self::WILDCARD === $this->subType)
            || (self::WILDCARD === $mediaType->type && self::WILDCARD === $mediaType->subType)
            || ($this->type === $mediaType->type && self::WILDCARD === $this->subType)
            || ($this->type === $mediaType->type && $this->subType === $mediaType->subType);
    }

    public function isSpecific(): bool
    {
        return self::WILDCARD !== $this->type && self::WILDCARD !== $this->subType;
    }

    public function isRange(): bool
    {
        return self::WILDCARD === $this->type || self::WILDCARD === $this->subType;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSubType(): string
    {
        return $this->subType;
    }

    public function toString(): string
    {
        return $this->type.self::SEPARATOR.$this->subType;
    }
}
