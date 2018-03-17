<?php declare(strict_types=1);

namespace Ellipse\Handlers\Exceptions;

use TypeError;

class ContainedControllerTypeException extends TypeError implements ControllerRequestHandlerExceptionInterface
{
    public function __construct(string $id, $value)
    {
        $template = "The value contained in the '%s' entry of the container is of type %s - object expected";

        $type = is_object($value) ? get_class($value) : gettype($value);

        $msg = sprintf($template, $id, $type);

        parent::__construct($msg);
    }
}
