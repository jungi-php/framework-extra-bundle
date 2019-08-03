Annotations
-----------

I assume Java coders are familiar with these annotations. In my opinion they're great features of the Spring Framework,
that's why I decided to implement them in the Symfony. They greatly eases life with handling requests and generating 
responses.

### RequestBody
Can be applied only on a method. Defines one parameter `name` which is used to match the controller argument.
Use it to convert the request body. In any case of malformed request body, unspecified request content type, 
400 response is generated.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\RequestBody;

class FooController
{
    /**
     * @RequestBody("request")
     */
    public function action(FooDto $request)
    {
        // ..
    }
}
```

### ResponseBody
Can be applied on a method or a whole class. When applied on class each action inherits silently this annotation. 
Use it to automatically convert any result from your controller. For conversion it uses the content negotiation mechanism
that is described [here](https://github.com/piku235/JungiFrameworkExtraBundle/blob/master/resources/doc/content_negotiation.md).

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

or

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Annotation\ResponseBody;

/**
 * @ResponseBody
 */
class FooController
{
    public function fooAction()
    {
        return array('hello' => 'world');
    }

    public function fooAction()
    {
        $result = new \stdClass();
        $result->bar = 'bar';

        return $result;
    }
}
```
