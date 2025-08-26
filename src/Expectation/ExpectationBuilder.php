<?php

namespace Nivseb\PhpMockServerConnector\Expectation;

use Nivseb\PhpMockServerConnector\Structs\MockServerExpectation;

class ExpectationBuilder
{
    public static function buildMockServerExpectation(MockServerExpectation $expectation): array
    {
        return [
            'times' => [
                'remainingTimes' => $expectation->times,
            ],
            'httpRequest'  => static::buildRequestForMockServerExpectation($expectation),
            'httpResponse' => static::buildResponseForMockServerExpectation($expectation),
        ];
    }

    protected static function buildRequestForMockServerExpectation(MockServerExpectation $expectation): array
    {
        $request = [
            'method' => $expectation->method,
            'path'   => $expectation->url,
        ];

        if ($expectation->pathParameters) {
            $request['pathParameters'] = static::buildPropertyMatcher($expectation->pathParameters);
        }
        if ($expectation->queryParameters) {
            $request['queryStringParameters'] = static::buildPropertyMatcher($expectation->queryParameters);
        }
        if ($expectation->requestHeaders) {
            $request['headers'] = static::buildPropertyMatcher($expectation->requestHeaders);
        }
        if ($expectation->requestBody) {
            $request['body'] = $expectation->requestBody;
        }

        return $request;
    }

    /**
     * @param array<string, array|bool|float|int|string> $properties
     */
    protected static function buildPropertyMatcher(array $properties): array
    {
        return array_map(
            fn (string $name, array|bool|float|int|string $expectedValue): array => [
                'name'   => $name,
                'values' => [$expectedValue],
            ],
            array_keys($properties),
            $properties,
        );
    }

    protected static function buildResponseForMockServerExpectation(MockServerExpectation $expectation): array
    {
        $response = ['statusCode' => $expectation->responseStatusCode];
        if ($expectation->responseBody) {
            $response['body'] = $expectation->responseBody;
        }

        if ($expectation->responseHeaders) {
            $response['headers'] = static::buildPropertyMatcher($expectation->responseHeaders);
        }

        return $response;
    }
}
