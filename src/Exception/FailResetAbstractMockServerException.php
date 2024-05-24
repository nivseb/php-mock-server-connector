<?php

namespace Nivseb\PhpMockServerConnector\Exception;

use Throwable;

class FailResetAbstractMockServerException extends AbstractMockServerException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('Failing to reset mock server expectations!', previous: $previous);
    }
}
