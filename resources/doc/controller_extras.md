Controller Trait
----------------

### Entity

A handy method for generating entity responses in controllers.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Controller\AbstractController;

class FooController extends AbstractController
{
    public function action()
    {
        return $this->entity(array('hello' => 'world'), 201);
    }
}
```
