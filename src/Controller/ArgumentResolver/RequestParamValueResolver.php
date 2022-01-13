<?php

namespace Jungi\FrameworkExtraBundle\Controller\ArgumentResolver;

use Jungi\FrameworkExtraBundle\Attribute\RequestParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Piotr Kugla <piku235@gmail.com>
 */
final class RequestParamValueResolver extends AbstractNamedValueArgumentValueResolver
{
    protected static string $attributeClass = RequestParam::class;

    /** @return string|int|float|bool|UploadedFile[]|UploadedFile|null */
    protected function getArgumentValue(NamedValueArgument $argument, Request $request): string|int|float|bool|array|UploadedFile|null
    {
        if ($this !== $result = $request->files->get($argument->getName(), $this)) {
            return $result;
        }

        return $request->request->get($argument->getName());
    }
}
