<?php

namespace Nivseb\PhpMockServerConnector\Structs;

class MockServerExpectation
{
    /**
     * @param array<string, array|bool|float|int|string> $pathParameters
     * @param array<string, array|bool|float|int|string> $queryParameters
     * @param array<string, array|bool|float|int|string> $requestHeaders
     */
    public function __construct(
        public readonly string $method,
        public readonly string $url,
        public int $responseStatusCode = 200,
        public null|array|string $responseBody = null,
        public ?array $responseHeaders = null,
        public int $atLeast = 1,
        public int $atMost = 1,
        public ?array $pathParameters = null,
        public ?array $queryParameters = null,
        public ?array $requestHeaders = null,
        public null|array|string $requestBody = null,
    ) {}
}
