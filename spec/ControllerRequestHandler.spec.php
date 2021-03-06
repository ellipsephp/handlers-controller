<?php

use function Eloquent\Phony\Kahlan\mock;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Ellipse\Resolvable\ResolvableCallable;
use Ellipse\Resolvable\DefaultResolvableCallableFactory;

use Ellipse\Handlers\ControllerContainer;
use Ellipse\Handlers\ControllerRequestHandler;
use Ellipse\Handlers\Exceptions\ContainedControllerTypeException;

describe('ControllerRequestHandler', function () {

    beforeEach(function () {

        $this->container = mock(ContainerInterface::class);
        $this->factory = mock(DefaultResolvableCallableFactory::class);

        allow(DefaultResolvableCallableFactory::class)->toBe($this->factory->get());

    });

    it('should implement RequestHandlerInterface', function () {

        $test = new ControllerRequestHandler($this->container->get(), 'Controller', 'action');

        expect($test)->toBeAnInstanceOf(RequestHandlerInterface::class);

    });

    describe('->handle()', function () {

        beforeEach(function () {

            $this->request = mock(ServerRequestInterface::class);
            $this->response = mock(ResponseInterface::class)->get();

        });

        context('when the controller retrieved from the container is an object', function () {

            beforeEach(function () {

                $controller = mock(['action' => function () {}])->get();

                $this->container->get->with('Controller')->returns($controller);

                $this->reflection = new ControllerContainer($this->container->get(), $this->request->get());
                $this->resolvable = mock(ResolvableCallable::class);

                $this->factory->__invoke->with([$controller, 'action'])->returns($this->resolvable);

            });

            context('when there is no attributes', function () {

                it('should resolve the controller action using an empty array of placeholders', function () {

                    $handler = new ControllerRequestHandler($this->container->get(), 'Controller', 'action');

                    $this->resolvable->value->with($this->reflection, [])->returns($this->response);

                    $test = $handler->handle($this->request->get());

                    expect($test)->toBe($this->response);

                });

            });

            context('when there is attributes', function () {

                it('should resolve the controller action using the attribute values as placeholders', function () {

                    $handler = new ControllerRequestHandler($this->container->get(), 'Controller', 'action', ['a1', 'a2']);

                    $this->request->getAttribute->with('a1')->returns('v1');
                    $this->request->getAttribute->with('a2')->returns('v2');

                    $this->resolvable->value->with($this->reflection, ['v1', 'v2'])->returns($this->response);

                    $test = $handler->handle($this->request->get());

                    expect($test)->toBe($this->response);

                });

            });

        });

        context('when the controller retrieved from the container is not an object', function () {

            it('should throw a ContainedControllerTypeException', function () {

                $this->container->get->with('Controller')->returns('controller');

                $handler = new ControllerRequestHandler($this->container->get(), 'Controller', 'action');

                $test = function () use ($handler) {

                    $handler->handle($this->request->get());

                };

                $exception = new ContainedControllerTypeException('Controller', 'controller');

                expect($test)->toThrow($exception);

            });

        });

    });

});
