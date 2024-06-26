<?php

namespace Nivseb\PhpMockServerConnector\Exception;

use Nivseb\PhpMockServerConnector\Expectation\RemoteExpectation;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class UnsuccessfulVerificationException extends AbstractMockServerException
{
    public function __construct(
        string $message,
        public RemoteExpectation $expectation,
        public ResponseInterface $response,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, previous: $previous);
    }
}
