<?php

namespace Nivseb\PhpMockServerConnector\Structs;

class MockServerExpectation
{
    public function __construct(
        public readonly string   $method,
        public readonly string   $url,
        public int               $responseStatusCode = 200,
        public array|string|null $responseBody = null,
        public ?array            $responseHeaders = null,
        public int               $times = 1,
        public ?array            $pathParameters = null,
        public ?array            $queryParameters = null,
        public ?array            $requestHeaders = null,
        public array|string|null $requestBody = null,
    )
    {
    }
}
