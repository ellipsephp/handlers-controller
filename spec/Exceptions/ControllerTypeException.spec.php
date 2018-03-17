<?php

use Ellipse\Handlers\Exceptions\ContainedControllerTypeException;
use Ellipse\Handlers\Exceptions\ControllerRequestHandlerExceptionInterface;

describe('ContainedControllerTypeException', function () {

    beforeEach(function () {

        $this->exception = new ContainedControllerTypeException('id', 'controller');

    });

    it('should implement ControllerRequestHandlerExceptionInterface', function () {

        expect($this->exception)->toBeAnInstanceOf(ControllerRequestHandlerExceptionInterface::class);

    });

    it('should extend TypeError', function () {

        expect($this->exception)->toBeAnInstanceOf(TypeError::class);

    });

});
