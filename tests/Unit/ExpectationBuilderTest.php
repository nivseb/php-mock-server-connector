<?php

namespace Tests\Unit;

use Nivseb\PhpMockServerConnector\Expectation\ExpectationBuilder;
use Nivseb\PhpMockServerConnector\Structs\MockServerExpectation;

it(
    'expectation is build with correct method',
    function (string $method): void {
        $expectation = new MockServerExpectation($method, '/');

        expect(ExpectationBuilder::buildMockServerExpectation($expectation))
            ->toMatchArray(['httpRequest' => ['method' => $method, 'path' => '/']]);
    }
)
    ->with(
        [
            'GET'     => ['GET'],
            'HEAD'    => ['HEAD'],
            'POST'    => ['POST'],
            'PUT'     => ['PUT'],
            'DELETE'  => ['DELETE'],
            'OPTIONS' => ['OPTIONS'],
        ]
    );

it(
    'expectation is build with correct path',
    function (string $path): void {
        $expectation = new MockServerExpectation('GET', $path);

        expect(ExpectationBuilder::buildMockServerExpectation($expectation))
            ->toMatchArray(['httpRequest' => ['method' => 'GET', 'path' => $path]]);
    }
)
    ->with(
        [
            'root'                => ['/'],
            'directory'           => ['/directory'],
            'subdirectory'        => ['/directory/subdirectory'],
            'file'                => ['/test.txt'],
            'file in directory'   => ['/directory/subdirectory/test.txt'],
            'with path parameter' => ['/directory/{placeHolder}/'],
        ]
    );

it(
    'expectation is build with correct times definition',
    function (int $min, int $max): void {
        $expectation = new MockServerExpectation('GET', '/', atLeast: $min, atMost: $max);

        expect(ExpectationBuilder::buildMockServerExpectation($expectation))
            ->toMatchArray(['times' => ['remainingTimes' => $max]]);
    }
)
    ->with(
        [
            'zero'      => [0, 0],
            'once'      => [1, 1],
            'twice'     => [2, 2],
            'ten'       => [10, 10],
            'up to ten' => [0, 10],
        ]
    );

it(
    'expectation is build with correct path parameters',
    function (array $pathParameters, array $expectedPathParameters): void {
        $expectation = new MockServerExpectation(
            'GET',
            '/',
            pathParameters: $pathParameters
        );
        expect(ExpectationBuilder::buildMockServerExpectation($expectation))
            ->toMatchArray(
                [
                    'httpRequest' => [
                        'method'         => 'GET',
                        'path'           => '/',
                        'pathParameters' => $expectedPathParameters,
                    ],
                ]
            );
    }
)
    ->with(
        [
            'single String' => [
                'pathParameters'         => ['pathParameterName' => 'myStringValue'],
                'expectedPathParameters' => [['name' => 'pathParameterName', 'values' => ['myStringValue']]],
            ],
            'single numeric' => [
                'pathParameters'         => ['pathParameterName' => 123],
                'expectedPathParameters' => [['name' => 'pathParameterName', 'values' => [123]]],
            ],
            'multiple values' => [
                'pathParameters'         => ['p1' => 123, 'p2' => 456],
                'expectedPathParameters' => [['name' => 'p1', 'values' => [123]], ['name' => 'p2', 'values' => [456]]],
            ],
        ]
    );

it(
    'expectation is build with correct query parameters',
    function (array $queryParameters, array $expectedQueryParameters): void {
        $expectation = new MockServerExpectation(
            'GET',
            '/',
            queryParameters: $queryParameters
        );
        expect(ExpectationBuilder::buildMockServerExpectation($expectation))
            ->toMatchArray(
                [
                    'httpRequest' => [
                        'method'                => 'GET',
                        'path'                  => '/',
                        'queryStringParameters' => $expectedQueryParameters,
                    ],
                ]
            );
    }
)
    ->with(
        [
            'single String' => [
                'queryParameters'         => ['queryParameterName' => 'myStringValue'],
                'expectedQueryParameters' => [['name' => 'queryParameterName', 'values' => ['myStringValue']]],
            ],
            'single numeric' => [
                'queryParameters'         => ['queryParameterName' => 123],
                'expectedQueryParameters' => [['name' => 'queryParameterName', 'values' => [123]]],
            ],
            'multiple values' => [
                'queryParameters'         => ['p1' => 123, 'p2' => 456],
                'expectedQueryParameters' => [['name' => 'p1', 'values' => [123]], ['name' => 'p2', 'values' => [456]]],
            ],
        ]
    );

it(
    'expectation is build with correct request headers',
    function (array $requestHeaders, array $expectedHeaders): void {
        $expectation = new MockServerExpectation(
            'GET',
            '/',
            requestHeaders: $requestHeaders
        );
        expect(ExpectationBuilder::buildMockServerExpectation($expectation))
            ->toMatchArray(
                [
                    'httpRequest' => [
                        'method'  => 'GET',
                        'path'    => '/',
                        'headers' => $expectedHeaders,
                    ],
                ]
            );
    }
)
    ->with(
        [
            'single String' => [
                'requestHeaders'  => ['headerName' => 'myStringValue'],
                'expectedHeaders' => [['name' => 'headerName', 'values' => ['myStringValue']]],
            ],
            'single numeric' => [
                'requestHeaders'  => ['headerName' => 123],
                'expectedHeaders' => [['name' => 'headerName', 'values' => [123]]],
            ],
            'multiple values' => [
                'requestHeaders'  => ['h1' => 123, 'h2' => 456],
                'expectedHeaders' => [['name' => 'h1', 'values' => [123]], ['name' => 'h2', 'values' => [456]]],
            ],
        ]
    );
