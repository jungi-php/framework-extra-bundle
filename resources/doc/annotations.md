Annotations
-----------

The core feature of this bundle, they mainly exist to facilitate handling requests and responses. 

#### `@RequestBody`

This annotation is one with the most features in it. It decodes the request message body and passes it to a method 
argument in a controller. It supports scalar, object, collection and file based method arguments.

##### Scalars

For scalar method arguments the `ConverterManager` is used if necessary.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Symfony\Component\Routing\Annotation\Route;

class MessageController
{
    /**
     * @Route("/messages", methods={"POST"})
     * @RequestBody("message")
     */
    public function sendMessage(string $message)
    {
        // ..
    }
}
```

##### Objects

In the contrary to the scalar types it uses the `MessageBodyMapperManager` to decode the request message body.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{
    /**
     * @Route("/users", methods={"POST"})
     * @RequestBody("data")
     */
    public function registerUser(UserRegistrationData $data)
    {
        // ..
    }
}
```

##### Collections

Unfortunately, at the current state PHP does not support declaring a collection with a desired type. In order to 
achieve that, the `argumentType` parameter has been provided. It accepts types of the well-known syntax `type[]`.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Symfony\Component\Routing\Annotation\Route;

class RoleController
{
    /**
     * @Route("/roles", methods={"POST"})
     * @RequestBody("rolesData", type="RoleCreationData[]")
     */
    public function createRoles(array $rolesData)
    {
        // ..
    }
}
```

##### Files

When the request message body contains a file, you can typehint your method argument as `UploadedFile`, `File`, 
`SplFileInfo` or `SplFileObject`. Thanks to the `TmpFileUtils` you can query those objects for any public method
like `getFilename()`, `getPathname()` and so on.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Symfony\Component\Routing\Annotation\Route;

class PictureController
{
    /**
     * @Route("/pictures", methods={"POST"})
     * @RequestBody("file")
     */
    public function savePicture(\SplFileInfo $file)
    {
        // ...
    }
}
```

###### UploadedFile

It's a bit a special one, it covers all public methods like `getClientOriginalName()` or `getClientMimeType()`.

The `getClientOriginalName()` method works thanks to the `Content-Disposition` header. In general this header is used 
in responses and in `multipart/form-data` requests. The `Content-Disposition` must be set to `inline` and the `filename` 
param must be provided.

On the contrary the `getClientMimeType()` works by using the `Content-Type` header.

**Example:**

```
POST /pictures HTTP/1.1
Content-Type: image/png
Content-Disposition: inline; filename=foo.png
```

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

class PictureController
{
    /**
     * @Route("/pictures", methods={"POST"})
     * @RequestBody("file")
     */
    public function savePicture(UploadedFile $file)
    {
        $filename = $file->getClientOriginalName(); // foo.png
        $mimeType = $file->getClientMimeType(); // image/png
        $path = $file->getPathname();
        // ...
    }
}
```

#### `@RequestParam`

It binds a request parameter to a method argument in a controller. It supports `multipart/form-data` requests and that 
means you can even access a file through this annotation.

Providing only the `value` in the annotation means the argument name, and the parameter name will be populated with
this value.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\RequestParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

class PictureController
{
    /**
     * @Route("/pictures", methods={"POST"})
     *
     * @RequestParam("file")
     * @RequestParam(name="description", argument="desc")
     */
    public function savePicture(UploadedFile $file, string $desc)
    {
        // ...
    }
}
```

#### `@QueryParam`

It binds a query parameter to a method argument in a controller.

Providing only the `value` in the annotation means the argument name, and the parameter name will be populated with
this value.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\QueryParam;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{
    /**
     * @Route("/users", methods={"GET"})
     *
     * @QueryParam("limit")
     */
    public function getUsers(?int $limit)
    {
        // ...
    }
}
```

#### `@QueryParams`

It is similar to the `@QueryParam`, but instead of a single query parameter it takes all query parameters to pass them
as a single method argument in a controller. This annotation requires an argument to be of an object type.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\QueryParams;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{
    /**
     * @Route("/users", methods={"GET"})
     *
     * @QueryParams("queryData")
     */
    public function getUsers(UserQueryData $queryData)
    {
        // ...
    }
}
```

#### `@RequestCookie`

It binds a cookie value to a method argument in a controller.

Providing only the `value` in the annotation means the argument name, and the parameter name will be populated with
this value.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\RequestCookie;
use Symfony\Component\Routing\Annotation\Route;

class SessionController
{
    /**
     * @Route("/sessions/current", methods={"GET"})
     *
     * @RequestCookie("sessionId")
     */
    public function getCurrentSessionInformation(string $sessionId)
    {
        // ...
    }
}
```

#### `@RequestHeader`

It binds a header value to a method argument in a controller. It requires to provide two options: `name` (a header name)
and `argumentName`.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\RequestHeader;
use Symfony\Component\Routing\Annotation\Route;

class ReportController
{
    /**
     * @Route("/reports/monthly-revenue", methods={"GET"})
     *
     * @RequestHeader(name="Accept-Language", argument="acceptableLanguage")
     */
    public function getMonthlyRevenueReport(string $acceptableLanguage)
    {
        // ...
    }

    /**
     * @Route("/reports/monthly-visitors", methods={"GET"})
     *
     * @RequestHeader(name="Accept", argument="acceptableMediaType")
     */
    public function getMonthlyVisitorsReport(array $acceptableMediaType)
    {
        // ...
    }
}
```

As you can see in the example, when a method argument is type hinted as `array` it will pass all values of a header.

#### `@ResponseBody`

You can use this annotation to encode your result from a controller method. In order to select an appropriate content 
type it uses the content negotiation that is described [here](https://github.com/piku235/JungiFrameworkExtraBundle/blob/master/resources/doc/content_negotiation.md).

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;

class FooController
{
    /**
     * @ResponseBody
     */
    public function action()
    {
        return array('hello' => 'world');
    }
}
```
