<?php

namespace Nivseb\PhpMockServerConnector\Server;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Nivseb\PhpMockServerConnector\Exception\FailCreateExpectationException;
use Nivseb\PhpMockServerConnector\Exception\FailResetMockServerException;
use Nivseb\PhpMockServerConnector\Exception\UnsuccessfulVerificationException;
use Nivseb\PhpMockServerConnector\Exception\VerificationFailException;
use Nivseb\PhpMockServerConnector\Expectation\ExpectationBuilder;
use Nivseb\PhpMockServerConnector\Expectation\RemoteExpectation;
use Nivseb\PhpMockServerConnector\Structs\MockServerExpectation;
use Psr\Http\Message\ResponseInterface;

class Connector
{
    protected Client $client;

    public function __construct(string $mockServerUrl)
    {
        $this->client = $this->buildClient($mockServerUrl);
    }

    /**
     * @throws FailResetMockServerException
     */
    public function reset(): void
    {
        try {
            $response = $this->client->put('/mockserver/reset');
            if ($response->getStatusCode() !== 200) {
                throw new FailResetMockServerException($response);
            }
        } catch (GuzzleException $exception) {
            throw new FailResetMockServerException(previous: $exception);
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
                    'json' => ExpectationBuilder::buildMockServerExpectation($expectation),
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
                            'atMost'  => $expectation->expectation->times,
                        ],
                    ],
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
            if (!$exception instanceof RequestException) {
                throw new VerificationFailException($expectation, $exception);
            }

            $response = $exception->getResponse();
            if (!$response) {
                throw new VerificationFailException($expectation, $exception);
            }

            throw new UnsuccessfulVerificationException(
                $this->getMessageFromResponse($response),
                $expectation,
                $response,
                $exception
            );
        }
    }

    protected function buildClient(string $mockServerUrl): Client
    {
        return new Client(['base_uri' => $mockServerUrl]);
    }

    protected function getMessageFromResponse(ResponseInterface $response): string
    {
        $result = $response->getBody()->read((int) $response->getHeaderLine('Content-Length'));

        $matches = [];
        if (preg_match('/^(.*), expected:<\{/', $result, $matches)) {
            return $matches[1];
        }

        return $result;
    }
}
