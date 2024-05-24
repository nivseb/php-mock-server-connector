<?php

namespace Nivseb\PhpMockServerConnector\Server;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Nivseb\PhpMockServerConnector\Exception\FailCreateExpectationException;
use Nivseb\PhpMockServerConnector\Exception\FailResetAbstractMockServerException;
use Nivseb\PhpMockServerConnector\Exception\UnsuccessfulVerificationException;
use Nivseb\PhpMockServerConnector\Exception\VerificationFailException;
use Nivseb\PhpMockServerConnector\Expectation\RemoteExpectation;
use Nivseb\PhpMockServerConnector\Structs\MockServerExpectation;
use Psr\Http\Message\ResponseInterface;

class Connector
{
    protected ?Client $client;

    public function __construct(string $mockServerUrl)
    {
        $this->client = new Client(['base_uri' => $mockServerUrl]);
    }

    /**
     * @throws FailResetAbstractMockServerException
     */
    public function reset(): void
    {
        try {
            $response = $this->client->put('/mockserver/reset');
            if ($response->getStatusCode() !== 200) {
                throw new FailResetAbstractMockServerException();
            }
        } catch (GuzzleException $exception) {
            throw new FailResetAbstractMockServerException($exception);
        }
    }

    /**
     * @throws FailCreateExpectationException
     */
    public function applyExpectation(MockServerExpectation $expectation): RemoteExpectation
    {
        try {
            $response = $this->client->put(
                '/mockserver/expectation',
                [
                    'json' => $this->buildMockServerExpectation($expectation)
                ]
            );
            if ($response->getStatusCode() !== 201) {
                throw new FailCreateExpectationException($expectation, $response);
            }
            return new RemoteExpectation(
                json_decode($response->getBody()->getContents())[0]->id,
                $expectation
            );
        } catch (GuzzleException $exception) {
            throw new FailCreateExpectationException($expectation, previous: $exception);
        }
    }

    protected function buildMockServerExpectation(MockServerExpectation $expectation): array
    {
        return [
            'times' => [
                'remainingTimes' => $expectation->times,
            ],
            'httpRequest' => $this->buildRequestForMockServerExpectation($expectation),
            'httpResponse' => $this->buildResponseForMockServerExpectation($expectation),
        ];
    }

    protected function buildRequestForMockServerExpectation(MockServerExpectation $expectation): array
    {
        $request = [
            'method' => $expectation->method,
            'path' => $expectation->url,
        ];

        if ($expectation->pathParameters) {
            $request['pathParameters'] = $this->buildPropertyMatcher($expectation->pathParameters);
        }
        if ($expectation->queryParameters) {
            $request['queryStringParameters'] = $this->buildPropertyMatcher($expectation->queryParameters);
        }
        if ($expectation->requestHeaders) {
            $request['headers'] = $this->buildPropertyMatcher($expectation->requestHeaders);
        }
        if ($expectation->requestBody) {
            $request['body'] = $expectation->requestBody;
        }

        return $request;
    }

    /**
     * @param array<string,string|int|float|bool> $properties
     */
    protected function buildPropertyMatcher(array $properties): array
    {
        return array_map(
            fn(string $name, string|int|float|bool $expectedValue): array => [
                'name' => $name,
                'values' => [$expectedValue],
            ],
            array_keys($properties),
            $properties,
        );
    }

    protected function buildResponseForMockServerExpectation(MockServerExpectation $expectation): array
    {
        $response = ['statusCode' => $expectation->responseStatusCode];
        if ($expectation->responseBody) {
            $response['body'] = $expectation->responseBody;
        }

        if ($expectation->responseHeaders) {
            $response['headers'] = $this->buildPropertyMatcher($expectation->responseHeaders);
        }

        return $response;
    }

    /**
     * @throws UnsuccessfulVerificationException
     * @throws VerificationFailException
     */
    public function verify(RemoteExpectation $expectation): void
    {
        try {
            $response = $this->client->put(
                '/mockserver/verify',
                [
                    'json' => [
                        'expectationId' => [
                            'id' => $expectation->uuid,
                        ],
                        'times' => [
                            'atLeast' => $expectation->expectation->times,
                            'atMost' => $expectation->expectation->times,
                        ],
                    ]
                ]
            );
            if ($response->getStatusCode() !== 202) {
                throw new UnsuccessfulVerificationException(
                    $this->getMessageFromResponse($response),
                    $expectation,
                    $response
                );
            }
        } catch (GuzzleException $exception) {
            if ($exception instanceof RequestException) {
                throw new UnsuccessfulVerificationException(
                    $this->getMessageFromResponse($exception->getResponse()),
                    $expectation,
                    $exception->getResponse(),
                    $exception
                );
            }
            throw new VerificationFailException(
                $expectation,
                $exception
            );
        }
    }

    protected function getMessageFromResponse(ResponseInterface $response): string
    {
        $result = $response->getBody()->read((int)$response->getHeaderLine('Content-Length'));

        $matches = [];
        if (preg_match('/^(.*), expected:<\{/', $result, $matches)) {
            return $matches[1];
        }

        return $result;
    }
}
