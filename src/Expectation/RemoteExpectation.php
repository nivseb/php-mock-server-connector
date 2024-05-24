<?php

namespace Nivseb\PhpMockServerConnector\Expectation;

use Nivseb\PhpMockServerConnector\Structs\MockServerExpectation;

class RemoteExpectation
{
    public function __construct(
        public string                $uuid,
        public MockServerExpectation $expectation
    )
    {
    }
}
