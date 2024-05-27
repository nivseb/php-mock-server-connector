<?php

namespace Nivseb\PhpMockServerConnector\Exception;

use Nivseb\PhpMockServerConnector\Expectation\RemoteExpectation;
use Throwable;

class VerificationFailException extends AbstractMockServerException
{
    public function __construct(
        public RemoteExpectation $expectation,
        ?Throwable $previous = null
    ) {
        parent::__construct('Fail to check verification for expectation!', previous: $previous);
    }
}
