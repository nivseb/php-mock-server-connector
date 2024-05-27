<?php

namespace Nivseb\PhpMockServerConnector\Exception;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class FailResetMockServerException extends AbstractMockServerException
{
    public function __construct(
        public ?ResponseInterface $response = null,
        ?Throwable $previous = null
    ) {
        parent::__construct('Failing to reset mock server expectations!', previous: $previous);
    }
}
