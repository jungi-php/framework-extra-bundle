Controller Extras
-----------------

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

### Normalized entity

Like above but with the ability of entity normalization.

```php
namespace App\Controller;

use Jungi\FrameworkExtraBundle\Controller\AbstractController;

class FooController extends AbstractController
{
    public function action()
    {
        return $this->normalizedEntity(array('hello' => 'world'), ['groups' => 'public'], 201);
    }
}
```
