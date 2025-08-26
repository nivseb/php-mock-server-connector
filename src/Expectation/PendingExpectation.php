<?php

namespace Nivseb\PhpMockServerConnector\Expectation;

use Nivseb\PhpMockServerConnector\Exception\AlreadyExpectedExpectationException;
use Nivseb\PhpMockServerConnector\Exception\FailCreateExpectationException;
use Nivseb\PhpMockServerConnector\Exception\MissingServerInitExceptionAbstract;
use Nivseb\PhpMockServerConnector\Server\MockServer;
use Nivseb\PhpMockServerConnector\Server\MockServerEndpoint;
use Nivseb\PhpMockServerConnector\Structs\MockServerExpectation;

class PendingExpectation
{
    protected MockServerExpectation $expectation;

    protected ?RemoteExpectation $remoteExpectation = null;

    public function __construct(
        protected MockServerEndpoint $mockServerEndpoint,
        string $method,
        string $url
    ) {
        $this->expectation = new MockServerExpectation($method, $this->mockServerEndpoint->getBasePath().$url);
    }

    /**
     * @throws FailCreateExpectationException
     * @throws MissingServerInitExceptionAbstract
     */
    public function __destruct()
    {
        if ($this->remoteExpectation) {
            return;
        }

        $this->run();
    }

    /**
     * @throws FailCreateExpectationException
     * @throws MissingServerInitExceptionAbstract
     */
    public function run(): void
    {
        $this->remoteExpectation = MockServer::applyExpectation($this->expectation);
        $this->mockServerEndpoint->registerExpectation($this->remoteExpectation);
    }

    /**
     * @param array<string, bool|float|int|string> $headers
     *
     * @throws AlreadyExpectedExpectationException
     */
    public function andReturn(int $statusCode, null|array|string $responseBody = null, ?array $headers = null): static
    {
        if ($this->remoteExpectation) {
            throw new AlreadyExpectedExpectationException($this->remoteExpectation);
        }

        $this->expectation->responseStatusCode = $statusCode;
        $this->expectation->responseBody       = $responseBody;
        $this->expectation->responseHeaders    = $headers;

        return $this;
    }

    /**
     * @param array<string, bool|float|int|string> $parameters
     *
     * @throws AlreadyExpectedExpectationException
     */
    public function withPathParameters(array $parameters): static
    {
        if ($this->remoteExpectation) {
            throw new AlreadyExpectedExpectationException($this->remoteExpectation);
        }

        $this->expectation->pathParameters = $parameters;

        return $this;
    }

    /**
     * @param array<string, bool|float|int|string> $parameters
     *
     * @throws AlreadyExpectedExpectationException
     */
    public function withQueryParameters(array $parameters): static
    {
        if ($this->remoteExpectation) {
            throw new AlreadyExpectedExpectationException($this->remoteExpectation);
        }

        $this->expectation->queryParameters = $parameters;

        return $this;
    }

    /**
     * @throws AlreadyExpectedExpectationException
     */
    public function withBody(array|string $body): static
    {
        if ($this->remoteExpectation) {
            throw new AlreadyExpectedExpectationException($this->remoteExpectation);
        }

        $this->expectation->requestBody = $body;

        return $this;
    }

    /**
     * @param array<string, bool|float|int|string> $headers
     *
     * @throws AlreadyExpectedExpectationException
     */
    public function withHeaders(array $headers): static
    {
        if ($this->remoteExpectation) {
            throw new AlreadyExpectedExpectationException($this->remoteExpectation);
        }

        $this->expectation->requestHeaders = $headers;

        return $this;
    }

    /**
     * @throws AlreadyExpectedExpectationException
     */
    public function between(int $minimum, int $maximum): static
    {
        if ($this->remoteExpectation) {
            throw new AlreadyExpectedExpectationException($this->remoteExpectation);
        }

        $this->expectation->atLeast = $minimum;
        $this->expectation->atMost  = $maximum;

        return $this;
    }

    /**
     * @throws AlreadyExpectedExpectationException
     */
    public function times(int $limit): static
    {
        return $this->between($limit, $limit);
    }

    /**
     * shorthand to ser time to 0.
     *
     * @throws AlreadyExpectedExpectationException
     */
    public function never(): static
    {
        return $this->times(0);
    }

    /**
     * shorthand to ser time to 1.
     *
     * @throws AlreadyExpectedExpectationException
     */
    public function once(): static
    {
        return $this->times(1);
    }
}
