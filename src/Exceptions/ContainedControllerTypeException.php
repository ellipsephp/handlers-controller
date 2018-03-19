<?php declare(strict_types=1);

namespace Ellipse\Handlers\Exceptions;

use TypeError;

use Ellipse\Exceptions\ContainerEntryTypeErrorMessage;

class ContainedControllerTypeException extends TypeError implements ControllerRequestHandlerExceptionInterface
{
    public function __construct(string $id, $value)
    {
        $msg = new ContainerEntryTypeErrorMessage($id, $value, 'object');

        parent::__construct((string) $msg);
    }
}
