JungiFrameworkExtraBundle
=========================

Just like the SensioFrameworkExtraBundle this bundle adds extra features on top of existing in the Symfony FrameworkBundle.
The main aim of this bundle is to facilitate the request/response operations.

[![Build Status](https://img.shields.io/travis/piku235/JungiFrameworkExtraBundle/master.svg?style=flat-square)](https://travis-ci.org/piku235/JungiFrameworkExtraBundle)

Annotations:
* **@RequestBody** - Maps/converts the request body content/parameters to the controller method argument.
* **@RequestQuery** - Converts the request query parameters to the controller method argument.
* **@RequestParam** - Converts a request body parameter to the controller method argument.
* **@QueryParam** - Converts a request query parameter to the controller method argument.
* **@RequestHeader** - Converts a request header to the controller method argument.
* **@RequestCookie** - Converts a request cookie to the controller method argument.
* **@ResponseBody** - Maps the controller method result to an appropriate entity response.

Also includes:
* **Entity response** - a response with the mapped entity to the text representation. Uses the content negotiation 
to decide to what media type map the entity.

### Quick insight

```php
namespace App\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Jungi\FrameworkExtraBundle\Annotation\RequestQuery;
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
     * @Route("", methods={"GET"})
     * @RequestQuery("filterData")
     * @ResponseBody
     */
    public function filterUsers(FilterUsersDTO $filterData)
    {
        // ..
        /** @var UserResource[] $filteredUsers */
        return $filteredUsers;
    }

    /**
     * @Route("", methods={"GET"})
     *
     * @QueryParam("limit")
     * @QueryParam("offset")
     */
    public function getUsers(?int $limit = null, ?int $offset = null)
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
        return new Response('', 201);
    }

    /**
     * @Route("/{userId}/residential-address", methods={"PUT"})
     * @RequestBody("resource")
     */
    public function changeResidentialAddress(string $userId, UserResidentialAddressResource $resource)
    {
        // ..
        return new Response('', 204);
    }

    /**
     * @Route("/{userId}/nickname", methods={"PUT"})
     * @RequestParam("nickname")
     */
    public function changeNickname(string $userId, string $nickname)
    {
        // ..
        return new Response('', 204);
    }
}
```

### Installation

```
composer require jungi/framework-extra-bundle
```

If you're using in your project symfony flex you're already done! Otherwise you need to enable the bundle manually.

```php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Jungi\FrameworkExtraBundle\JungiFrameworkExtraBundle(),
    );
    // ...
}
```

### Documentation
[click me](https://github.com/piku235/JungiFrameworkExtraBundle/blob/master/resources/doc/index.md)
