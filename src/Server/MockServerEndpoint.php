<?php

namespace Nivseb\PhpMockServerConnector\Server;

use Nivseb\PhpMockServerConnector\Exception\UnsuccessfulVerificationException;
use Nivseb\PhpMockServerConnector\Exception\VerificationFailException;
use Nivseb\PhpMockServerConnector\Expectation\PendingExpectation;
use Nivseb\PhpMockServerConnector\Expectation\RemoteExpectation;

class MockServerEndpoint
{
    /** @var array<RemoteExpectation> */
    protected array $expectations = [];

    public function __construct(
        protected string $basePath = '/'
    ) {
        MockServer::registerEndpoint($this);
    }

    public function allows(string $method, string $url): PendingExpectation
    {
        return new PendingExpectation($this, $method, $url);
    }

    public function registerExpectation(RemoteExpectation $expectation): void
    {
        $this->expectations[$expectation->uuid] = $expectation;
    }

    /**
     * @return array<string, MockServerEndpoint>
     */
    public function getExpectations(): array
    {
        return $this->expectations;
    }

    public function getExpectation(string $uuid): ?RemoteExpectation
    {
        return $this->expectations[$uuid] ?? null;
    }

    public function resetExpectations(): void
    {
        $this->expectations = [];
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @throws UnsuccessfulVerificationException
     * @throws VerificationFailException
     */
    public function verify(Connector $connector): void
    {
        foreach ($this->expectations as $expectation) {
            $connector->verify($expectation);
        }
    }
}
