<?php

namespace Nivseb\PhpMockServerConnector\Exception;

use Nivseb\PhpMockServerConnector\Expectation\RemoteExpectation;

class AlreadyExpectedExpectationException extends AbstractMockServerException
{
    public function __construct(
        public RemoteExpectation $remoteExpectation
    ) {
        parent::__construct('Expectation is already applied to the mock server!');
    }
}
