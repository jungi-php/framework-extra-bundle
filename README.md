# JungiFrameworkExtraBundle

[![Build Status](https://github.com/jungi-php/framework-extra-bundle/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/jungi-php/framework-extra-bundle/actions)
![PHP](https://img.shields.io/packagist/php-v/jungi/framework-extra-bundle)

This bundle adds additional features whose main purpose is to facilitate request/response operations.

Attributes (aka annotations):

* [`RequestBody`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#requestbody)
* [`RequestHeader`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#requestheader)
* [`RequestCookie`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#requestcookie)
* [`RequestParam`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#requestparam)
* [`QueryParam`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#queryparam)
* [`QueryParams`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#queryparams)
* [`ResponseBody`](https://piku235.gitbook.io/jungiframeworkextrabundle/attributes#responsebody)

## Installation

Before you install, decide which version suits you the most:
* `^1.4` - for Symfony `^5.3`
* `1.3` - for Symfony `<5.3`(bugfixes only)

```text
composer require jungi/framework-extra-bundle
```

## Documentation

[GitBook](https://piku235.gitbook.io/jungiframeworkextrabundle)

## Quick insight

### #[Attribute]

```php
namespace App\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Jungi\FrameworkExtraBundle\Attribute\QueryParams;
use Jungi\FrameworkExtraBundle\Attribute\RequestBody;
use Jungi\FrameworkExtraBundle\Attribute\ResponseBody;
use Jungi\FrameworkExtraBundle\Attribute\QueryParam;
use Jungi\FrameworkExtraBundle\Attribute\RequestParam;

#[Route('/users')]
class UserController
{
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
    #[ResponseBody]
    public function filterUsers(#[QueryParams] FilterUsersDto $filterData)
    {
        // ..
        /** @var UserData[] $filteredUsers */
        return $filteredUsers;
    }
}
```

### @Annotation

```php
namespace App\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Jungi\FrameworkExtraBundle\Annotation\QueryParams;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;
use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Jungi\FrameworkExtraBundle\Annotation\RequestParam;

/**
 * @Route("/users")
 */
class UserController
{
    /**
     * @Route("/{userId}/residential-address", methods={"PATCH"})
     * @RequestBody("data")
     */
    public function changeResidentialAddress(string $userId, UserResidentialAddressData $data)
    {
        // ..
    }

    /**
     * @Route("/{userId}/files/{fileName}", methods={"PUT"})
     * @RequestBody("file")
     */
    public function uploadFile(string $userId, string $fileName, UploadedFile $file)
    {
        // ..
    }

    /**
     * @Route("/{userId}/avatar", methods={"PATCH"})
     *
     * @RequestParam("file")
     * @RequestParam("title")
     */
    public function replaceAvatar(string $userId, UploadedFile $file, string $title)
    {
        // ..
    }

    /**
     * @Route(methods={"GET"})
     *
     * @QueryParam("limit")
     * @QueryParam("offset")
     */
    public function getUsers(?int $limit = null, ?int $offset = null)
    {
        // ..
    }

    /**
     * @Route(methods={"GET"})
     *
     * @QueryParams("filterData")
     * @ResponseBody
     */
    public function filterUsers(FilterUsersDto $filterData)
    {
        // ..
        /** @var UserData[] $filteredUsers */
        return $filteredUsers;
    }
}
```
