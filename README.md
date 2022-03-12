# JungiFrameworkExtraBundle

[![Build Status](https://github.com/jungi-php/framework-extra-bundle/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/jungi-php/framework-extra-bundle/actions)
![PHP](https://img.shields.io/packagist/php-v/jungi/framework-extra-bundle)

This bundle adds additional features whose main purpose is to facilitate request/response operations.

Attributes:

* [`RequestBody`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#requestbody)
* [`RequestHeader`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#requestheader)
* [`RequestCookie`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#requestcookie)
* [`RequestParam`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#requestparam)
* [`QueryParam`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#queryparam)
* [`QueryParams`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#queryparams)

## Installation

Before you install, decide which version suits you the most.

* `^2.0` (current) - for Symfony `^6.0` and PHP `>=8.0.2`
* `^1.4` (maintained) - for Symfony `^5.3` and PHP `>=7.2.9`

```text
composer require jungi/framework-extra-bundle
```

## Documentation

[GitBook](https://piku235.gitbook.io/jungiframeworkextrabundle)

## Quick insight

```php
namespace App\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Jungi\FrameworkExtraBundle\Attribute\QueryParams;
use Jungi\FrameworkExtraBundle\Attribute\RequestBody;
use Jungi\FrameworkExtraBundle\Attribute\QueryParam;
use Jungi\FrameworkExtraBundle\Attribute\RequestParam;
use Jungi\FrameworkExtraBundle\Controller\ControllerTrait;

#[Route('/users')]
class UserController
{
    use ControllerTrait;

    #[Route('/{userId}/residential-address', methods: ['PATCH'])]
    public function changeResidentialAddress(string $userId, #[RequestBody] UserResidentialAddressData $data)
    {
        // ..
    }

    #[Route('/{userId}/files/{fileName}', methods: ['PUT'])]
    public function uploadFile(string $userId, string $fileName, #[RequestBody] UploadedFile $file)
    {
        // ..
    }

    #[Route('/{userId}/avatar', methods: ['PATCH'])]
    public function replaceAvatar(string $userId, #[RequestParam] UploadedFile $file,  #[RequestParam] string $title)
    {
        // ..
    }

    #[Route(methods: ['GET'])]
    public function getUsers(#[QueryParam] ?int $limit = null, #[QueryParam] ?int $offset = null)
    {
        // ..
    }

    #[Route(methods: ['GET'])]
    public function filterUsers(#[QueryParams] FilterUsersDto $filterData)
    {
        // ..
        /** @var UserData[] $filteredUsers */
        return $this->entity($filteredUsers);
    }
}
```