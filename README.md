JungiFrameworkExtraBundle
=========================

Just like the SensioFrameworkExtraBundle this bundle adds extra features on top of existing in the Symfony FrameworkBundle.

[![Build Status](https://img.shields.io/travis/piku235/JungiFrameworkExtraBundle/master.svg?style=flat-square)](https://travis-ci.org/piku235/JungiFrameworkExtraBundle)

Includes:
* Annotations: RequestBody, ResponseBody,
* Entity responses - a normal response with the converted entity to text format

### Quick insight

```php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Jungi\FrameworkExtraBundle\Annotation\RequestBody;
use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;

/**
 * @Route("/users")
 */
class UserController
{
    /**
     * @Route("/{userId}/residential-address", methods={"GET"})
     * @ResponseBody
     */
    public function getResidentialAddress(string $userId)
    {
        return new UserResidentialAddressResource('street', 'city', 'province', 'country_code');
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
