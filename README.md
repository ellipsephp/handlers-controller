# Request handler controller

This package provides a [Psr-15](https://www.php-fig.org/psr/psr-15/) request handler proxying a class method using a [Psr-11](https://www.php-fig.org/psr/psr-11/) container.

**Require** php >= 7.0

**Installation** `composer require ellipse/handlers-controller`

**Run tests** `./vendor/bin/kahlan`

- [Using controllers as request handlers](#using-controllers-as-request-handlers)
- [Example using auto wiring](#example-using-auto-wiring)

## Using controllers as request handlers

The class `Ellipse\Handlers\ControllerRequestHandler` takes an implementation of `Psr\Container\ContainerInterface`, a class name, a method name and an optional array of request attribute names as parameters. Its `->handle()` method retrieve an instance of the class from the container and call its method with the given name in order to return a response.

The controller method is executed by using the container to retrieve values for its type hinted parameters. Request attribute values matching the given request attribute names are used for the non-type hinted parameters, in the order they are listed.

Also when the controller method has a parameter type hinted as `Psr\Http\Message\ServerRequestInterface`, the actual Psr-7 request received by the request handler is used. It means when a middleware create a new request (since Psr-7 requests are immutable) the controller method receive this new request.

```php
<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface;

use App\SomeService;
use App\SomeOtherService;

class SomeController
{
    public function __construct(SomeService $service)
    {
        //
    }

    public function index(SomeOtherService $service)
    {
        // return a Psr-7 response
    }

    public function show(SomeOtherService $service, $some_id)
    {
        // return a Psr-7 response
    }

    public function store(ServerRequestInterface $request)
    {
        // return a Psr-7 response
    }
}
```

```php
<?php

namespace App;

use SomePsr11Container;

use Ellipse\Handlers\ControllerRequestHandler;

use App\Controllers\SomeController;

// Get some Psr-11 container.
$container = new SomePsr11Container;

// Register the controller in the container.
$container->set(SomeController::class, function ($container) {

    return new SomeController(new SomeService);

});

// Register some services in the container.
$container->set(SomeOtherService::class, function ($container) {

    return new SomeOtherService;

});

// Those request handlers are using the Psr-11 container, controller class names, methods and attributes.
$handler1 = new ControllerRequestHandler($container, SomeController::class, 'index');
$handler2 = new ControllerRequestHandler($container, SomeController::class, 'show', ['some_id']);
$handler3 = new ControllerRequestHandler($container, SomeController::class, 'store');

// The request handler ->handle() method proxy SomeController index method.
// The contained instance of SomeOtherService is passed to the method.
$response = $handler1->handle($request);

// Here the request handler ->handle() method proxy SomeController show method.
// The contained instance of SomeOtherService is passed to the method.
// The $some_id parameter will receive the request 'some_id' attribute value.
$response = $handler2->handle($request);

// Here the request handler ->handle() method proxy SomeController store method.
// The $request parameter will receive the actual Psr-7 request received by the request handler.
$response = $handler3->handle($request);
```

## Example using auto wiring

It can be cumbersome to register every controller classes in the container. Here is how to auto wire controller classes using the `Ellipse\Container\ReflectionContainer` class from the [ellipse/container-reflection](https://github.com/ellipsephp/container-reflection) package.

```php
<?php

namespace App;

use SomePsr11Container;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Handlers\ControllerRequestHandler;

use App\Controllers\SomeController;

// Get some Psr-11 container.
$container = new SomePsr11Container;

// Decorate the container with a reflection container.
$container = new ReflectionContainer($container);

// The request handlers are using the reflection container.
$handler = new ControllerRequestHandler($container, SomeController::class, 'index');

// An instance of SomeController is built.
$response = $handler->handle($request);
```
