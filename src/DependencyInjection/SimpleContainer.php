<?php

namespace Jungi\FrameworkExtraBundle\DependencyInjection;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class SimpleContainer implements ContainerInterface
{
    private $entries;

    public function __construct(array $entries)
    {
        $this->entries = $entries;
    }

    public function get($id)
    {
        if (!isset($this->entries[$id])) {
            throw $this->createNotFoundException(sprintf('Entry "%s" not found in the container.', $id));
        }

        return $this->entries[$id];
    }

    public function has($id)
    {
        return isset($this->entries[$id]);
    }

    private function createNotFoundException(string $message): NotFoundExceptionInterface
    {
        return new class($message) extends \InvalidArgumentException implements NotFoundExceptionInterface {};
    }
}
