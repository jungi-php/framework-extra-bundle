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

## Development

With the new release of Symfony v6.3 [mapping request data to typed objects](https://symfony.com/blog/new-in-symfony-6-3-mapping-request-data-to-typed-objects), the development and further need for this bundle has come to an end.

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
