<?php declare(strict_types=1);

namespace Ellipse\Handlers;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Container\ReflectionContainer;
use Ellipse\Container\OverriddenContainer;
use Ellipse\Resolvable\DefaultResolvableCallableFactory;
use Ellipse\Handlers\Exceptions\ContainedControllerTypeException;

class ControllerRequestHandler implements RequestHandlerInterface
{
    /**
     * The container.
     *
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * The resolvable callable factory.
     *
     * @var \Ellipse\Resolvable\DefaultResolvableCallableFactory
     */
    private $factory;

    /**
     * The container id to use to retrieve the controller.
     *
     * @var string
     */
    private $controller;

    /**
     * The controller method.
     *
     * @var string
     */
    private $method;

    /**
     * The request attributes to inject in the method.
     *
     * @var array
     */
    private $attributes;

    /**
     * Set up a controller request handler with the given container, container
     * id, method and attributes.
     *
     * @param \Psr\Container\ContainerInterface $factory
     * @param string                            $controller
     * @param string                            $method
     * @param array                             $attributes
     */
    public function __construct(ContainerInterface $container, string $controller, string $method, array $attributes = [])
    {
        $this->container = $container;
        $this->factory = new DefaultResolvableCallableFactory;
        $this->controller = $controller;
        $this->method = $method;
        $this->attributes = $attributes;
    }

    /**
     * Return a response from the controller method. Use a controller container
     * using the given request to get the controller and execute the controller
     * method using the resolvable callable factory.
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Ellipse\Handlers\Exceptions\ContainedControllerTypeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $container = new ControllerContainer($this->container, $request);

        $placeholders = array_map([$request, 'getAttribute'], $this->attributes);

        $controller = $container->get($this->controller);

        if (is_object($controller)) {

            $action = [$controller, $this->method];

            return ($this->factory)($action)->value($container, $placeholders);

        }

        throw new ContainedControllerTypeException($this->controller, $controller);
    }
}
